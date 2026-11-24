# Keamanan Kasirin

Dokumen ini menjelaskan kontrol keamanan yang sudah diterapkan di aplikasi, daftar periksa sebelum dipublikasikan, serta risiko sisa (_residual risk_) yang **tidak bisa ditutup hanya dari kode aplikasi**.

> Tidak ada aplikasi yang 100% bebas celah. Dokumen ini adalah hasil audit internal: semua temuan yang bisa diperbaiki di level kode sudah diperbaiki dan diberi regression test. Sisa risiko ada di level hosting, operasional, dan kebijakan pengguna.

---

## 1. Kontrol yang sudah diterapkan

### Autentikasi & sesi
- Password di-hash dengan bcrypt/argon (bawaan Laravel), tidak pernah disimpan atau dicatat dalam bentuk plaintext.
- Kebijakan password terpusat di `AppServiceProvider::boot()` melalui `Password::defaults()`:
  - produksi: minimal 12 karakter, huruf besar + kecil + angka;
  - lokal/testing: minimal 8 karakter.
- Login dibatasi (10 percobaan/menit per email + IP) dan memakai pesan error generik.
- Forgot-password dibatasi 5/menit; respons selalu sama untuk email terdaftar maupun tidak (mencegah enumerasi akun).
- Reset password membatasi 5/menit, memakai token sekali pakai, dan `logoutOtherDevices()` setelah password diganti.
- Ganti password membutuhkan `current_password`, minimal 6/menit.
- Ganti email membutuhkan `current_password` (mencegah session hijack mengganti alamat pemulihan akun).
- Sesi dienkripsi (`SESSION_ENCRYPT=true`), cookie `HttpOnly`, `SameSite=Lax`, dan `Secure` otomatis di HTTPS.
- `AuthenticateSession` memaksa logout semua perangkat lain saat password berubah.
- Middleware `EnsureUserIsActive` memutus sesi pengguna yang dinonaktifkan owner (hanya `is_active === false` yang dianggap nonaktif, baris lama `null` tetap aktif).
- Registrasi publik dibatasi 5/menit.

### Otorisasi & isolasi multi-tenant
- RBAC Spatie: `owner`, `kasir`, `gudang`, dengan middleware `role:` di setiap grup route.
- Global scope `BelongsToTenant` menyaring query berdasarkan `tenant_id` pengguna yang login.
- Karena scope global tidak aktif di CLI/queue, semua service kritis (`SaleService`, `StockService`, `InvoiceNumberService`) memakai filter `tenant_id` eksplisit + `withoutGlobalScope('tenant')` hanya untuk pengecekan lintas tenant.
- IDOR diuji dengan regression test: transaksi, PDF, customer, dan produk tenant lain menghasilkan 404.
- Metrik finansial dan grafik arus kas dashboard hanya untuk `owner` (route `dashboard.chart` mengembalikan 403 untuk role lain).
- Owner terakhir tidak boleh menghapus akunnya sendiri (mencegah tenant tanpa admin).
- Policy transaksi mencegah kasir melakukan void transaksi tenant lain atau di luar haknya.

### Validasi input & output
- Semua input divalidasi melalui FormRequest / `$request->validate()` dengan batas panjang.
- Unggahan gambar (avatar, wallpaper, logo) divalidasi MIME + ekstensi + ukuran, disimpan dengan nama acak di disk `public`.
- CSV export disanitasi dengan `App\Support\CsvSanitizer` untuk mencegah _formula injection_ (`=`, `+`, `-`, `@`, tab, CR).
- Notifikasi email membersihkan karakter baris baru pada nama produk/SKU (mencegah header/body injection).
- Redirect notifikasi hanya diizinkan ke URL relatif internal (`safeRedirectUrl()`), menutup open redirect.
- Blade meng-escape output secara default; tidak ada `{!! !!}` pada data pengguna.

### Transaksi & integritas data
- Penjualan, pembayaran, pengurangan stok, dan pencatatan stock log berjalan dalam satu `DB::transaction`.
- Stok dibaca ulang dengan `lockForUpdate()` sebelum dikurangi; stok negatif ditolak di dalam lock.
- Nomor invoice dibuat unik per tenant + tanggal dengan lock pada baris tenant dan constraint `unique(tenant_id, invoice_number)` di database.
- Void transaksi mengembalikan stok secara atomik dan mencatat jejak audit.
- Semua perubahan stok tercatat di `stock_logs` (stok sebelum/sesudah, user, alasan).

### Header & browser hardening
Middleware `SecurityHeaders` dipasang global dan mengirim:

| Header | Nilai |
|---|---|
| `Content-Security-Policy` | `default-src 'self'`, `object-src 'none'`, `frame-ancestors 'none'`, `form-action 'self'`, nonce per request untuk script |
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `DENY` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | kamera, mikrofon, geolokasi, payment, USB, browsing-topics dimatikan |
| `Cross-Origin-Opener-Policy` | `same-origin` |
| `Cross-Origin-Resource-Policy` | `same-origin` |
| `X-Permitted-Cross-Domain-Policies` | `none` |
| `Strict-Transport-Security` | `max-age=31536000` (hanya saat HTTPS) |
| `Cache-Control` | `no-store, private` + `X-Robots-Tag: noindex` untuk respons terautentikasi |

- CSP memakai nonce per request; `script-src 'unsafe-inline'` sudah dihapus. `'unsafe-eval'` masih diperlukan oleh Alpine.js.
- `style-src 'unsafe-inline'` masih diperlukan oleh atribut `style` dinamis dan tema.
- `X-Powered-By` dan `Server` dihapus di level framework **dan** level SAPI PHP.
- Tidak ada handler inline (`onsubmit`, `onclick`); konfirmasi hapus memakai `data-confirm` + satu delegated listener, atau `x-on:click` Alpine.
- Aset statis juga diberi header keamanan melalui `Caddyfile` dan `public/.htaccess`.

### Rate limiting endpoint sensitif

| Endpoint | Batas |
|---|---|
| `register` | 5/menit |
| `login` | 10/menit (per email + IP) |
| `forgot-password` / `reset-password` | 5/menit |
| `confirm-password` / `password.update` | 6/menit |
| `profile.avatar.update` | 10/menit |
| `appearance.update` | 10/menit |
| `appearance.theme` | 30/menit |
| `dashboard.chart` | 60/menit |
| `pos.lookup` / `pos.store` | 60/menit |
| `transactions.pdf` | 10/menit |
| `reports.export` | 10/menit |

### Privasi & konfigurasi
- `AppServiceProvider` memaksa skema HTTPS dan root URL di produksi (`URL::forceScheme('https')`).
- Proxy tepercaya diatur via `config/trustedproxy.php` + `TRUSTED_PROXIES` (wajib `*` di Vercel/container agar HTTPS terdeteksi).
- `.env` tidak masuk repository; `.env.example` dan `.env.vercel.example` berisi placeholder.
- `APP_DEBUG=false` dan `LOG_LEVEL=warning` untuk produksi.
- Tidak ada request ke CDN/font pihak ketiga (font Plus Jakarta Sans di-host sendiri), sehingga CSP tetap ketat dan tidak ada kebocoran IP pengguna ke pihak ketiga.

### Dependensi
- `composer audit --locked` bersih.
- `npm audit` 0 kerentanan.
- Semua dependensi langsung memakai versi terbaru; entri `npm outdated --all` yang tersisa hanyalah dependensi bersarang yang dipatok oleh parent terbaru.

### Pengujian keamanan
`tests/Feature/SecurityHardeningTest.php` dan `tests/Unit/CsvSanitizerTest.php` mencakup antara lain:
- header keamanan & CSP nonce pada setiap `<script>`,
- penolakan akses lintas tenant (transaksi + PDF),
- owner terakhir tidak bisa dihapus,
- respons forgot-password yang seragam,
- perlindungan formula injection CSV,
- batas panjang input login,
- penolakan pengguna nonaktif.

---

## 2. Checklist sebelum publikasi

### Environment
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` unik dan sudah di-`php artisan key:generate`.
- [ ] `APP_URL` memakai `https://` domain final.
- [ ] `SESSION_SECURE_COOKIE=true` (atau pastikan HTTPS terdeteksi via proxy).
- [ ] `SESSION_ENCRYPT=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`.
- [ ] `LOG_LEVEL=warning` (jangan `debug`).
- [ ] Kredensial database, mail, dan storage hanya ada di environment hosting, bukan di repository.
- [ ] `TRUSTED_PROXIES` diisi IP load balancer, atau `*` bila platform tidak memberi IP tetap (Vercel).

### Server & transport
- [ ] HTTPS + redirect paksa dari HTTP.
- [ ] HSTS aktif (middleware sudah mengirim saat `secure()`).
- [ ] Document root mengarah ke `public/`, bukan root proyek (file `.env` tidak boleh bisa diunduh).
- [ ] `php artisan config:cache route:cache view:cache` dijalankan saat deploy.
- [ ] Direktori `storage/` dan `bootstrap/cache/` writable hanya oleh user aplikasi.
- [ ] Akses SSH/panel admin dibatasi IP dan memakai 2FA.

### Operasional
- [ ] Backup database harian + uji restore.
- [ ] Queue worker berjalan untuk notifikasi stok.
- [ ] Log dipantau; alert untuk lonjakan 4xx/5xx dan login gagal.
- [ ] Rotasi kredensial demo (`owner@demo.com` dkk.) atau hapus akun demo sebelum produksi.
- [ ] `php artisan migrate --force` dijalankan lewat pipeline, bukan manual tanpa catatan.

### Setelah deploy
- [ ] Cek header dengan `curl -I https://domain-anda/`.
- [ ] Pastikan `/storage/avatars/...` hanya berisi file yang diunggah lewat aplikasi.
- [ ] Uji login, reset password, POS, PDF, export CSV, dan notifikasi.
- [ ] Pastikan halaman error produksi tidak menampilkan stack trace.

---

## 3. Risiko sisa yang perlu dipahami

1. **`'unsafe-eval'` pada CSP** — Alpine.js mengevaluasi ekspresi dengan `new Function`. Menghapusnya akan mematikan seluruh interaksi Alpine. Mitigasi: semua script hanya berasal dari origin sendiri + nonce; tidak ada konten pengguna yang dirender sebagai JavaScript.
2. **`'unsafe-inline'` pada `style-src`** — dibutuhkan oleh atribut `style` dinamis (blur, wallpaper, chart). Dampaknya terbatas pada CSS, bukan eksekusi script.
3. **Penyimpanan unggahan** — di Vercel/container, filesystem bersifat sementara. Avatar/wallpaper/logo akan hilang saat redeploy kecuali memakai S3/R2/Blob. Ini masalah ketahanan data, bukan kebocoran.
4. **Rate limit berbasis cache** — jika cache dibersihkan atau memakai driver yang tidak persisten, hitungan throttle ikut reset. Gunakan Redis/database di produksi untuk konsistensi.
5. **Email** — verifikasi email tidak diwajibkan pada alur saat ini. Siapa pun yang menguasai email bisa memakai fitur reset password. Aktifkan verifikasi email bila akun dipakai untuk data sensitif.
6. **Multi-tenant berbasis scope** — kode baru yang menambahkan query mentah (`DB::table`, `withoutGlobalScope`) wajib menambahkan filter `tenant_id` manual. Selalu tambahkan regression test lintas tenant saat menambah fitur.
7. **Akun demo** — seeder membuat password `password`. Jangan pernah menjalankan `DemoSeeder` di produksi, atau segera ganti/hapus akun tersebut.
8. **Dependensi** — audit bersih hari ini bukan jaminan besok. Jalankan `composer audit` dan `npm audit` minimal mingguan (atau aktifkan Dependabot).
9. **Lapisan hosting** — WAF, DDoS protection, patch OS/PHP, dan keamanan database adalah tanggung jawab platform/penyedia. Aplikasi tidak bisa menutup serangan volumetrik di level jaringan.
10. **Batas audit** — audit dilakukan pada kode aplikasi dan konfigurasi repository. Infrastruktur aktual (DNS, TLS, IAM, backup) harus diverifikasi terpisah oleh pemilik deployment.

---

## 4. Melaporkan kerentanan

Jika menemukan celah, jangan membuka issue publik. Kirim detail (langkah reproduksi, dampak, versi) ke pemilik repository secara privat. Perbaikan akan diprioritaskan sebelum detail dipublikasikan.
