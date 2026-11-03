# Kasirin — Kasir, Keuangan & Inventaris UMKM

Kasirin adalah aplikasi web **multi-tenant** untuk membantu UMKM mengelola penjualan, keuangan, dan stok barang dalam satu tempat. Dibangun dengan Laravel 12, aplikasi ini mencakup POS (point of sale), invoice PDF otomatis, pencatatan pemasukan/pengeluaran, laporan laba rugi, peringatan stok menipis, serta manajemen tim dengan hak akses berbeda.

Setiap bisnis yang mendaftar mendapatkan workspace-nya sendiri. Data antar-bisnis terisolasi secara otomatis melalui global scope `tenant_id`.

---

## Fitur Utama

### Penjualan & Kasir
- POS dengan pencarian produk, pemindaian barcode/SKU, keranjang, diskon, pajak, dan pembayaran tunai/transfer/QRIS.
- Transaksi disimpan secara atomik menggunakan `DB::transaction` — stok dan detail transaksi tidak akan setengah tersimpan.
- Nomor invoice berurutan otomatis per hari dengan prefix yang dapat diatur.
- Invoice PDF siap unduh (Dompdf).
- Pembatalan transaksi (void) oleh owner dengan pengembalian stok otomatis.
- Riwayat transaksi dengan filter tanggal, tipe, status pembayaran, dan pencarian.

### Inventaris
- Produk, kategori, SKU, barcode, harga beli, harga jual, satuan, dan batas minimum stok.
- Pergerakan stok: barang masuk, barang keluar, penyesuaian/opname, penjualan, dan pembatalan.
- Jejak audit stok lengkap (`stock_logs`) berisi stok sebelum/sesudah dan petugas.
- Notifikasi stok menipis melalui email dan pusat notifikasi (queued job + anti-spam 6 jam).
- Nilai stok dan daftar produk yang perlu restock di dashboard.

### Keuangan & Laporan
- Pencatatan pemasukan lain dan pengeluaran operasional.
- Ringkasan pemasukan, pengeluaran, dan selisih bersih.
- Laporan laba rugi: omzet, HPP, laba kotor, pemasukan lain, beban, dan laba bersih.
- Grafik arus kas harian dan produk terlaris.
- Ekspor laporan ke CSV (kompatibel dengan Excel, termasuk BOM UTF-8).

### Multi-tenant, Tim & Keamanan
- Registrasi otomatis membuat tenant baru, 4 kategori default, dan akun owner.
- Role-based access control: **owner**, **kasir**, **gudang** (Spatie Permission).
- Policy transaksi, middleware role, dan rate limiting pada endpoint POS (`60 request/menit`).
- Pengaturan bisnis: nama, alamat, telepon, email, logo, prefix invoice, dan tarif pajak.
- Profil pengguna, ganti password, dan hapus akun.

---

## Tumpukan Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 12, PHP ≥ 8.2 |
| Autentikasi | Laravel Breeze (Blade) |
| Hak akses | spatie/laravel-permission 8 |
| PDF | barryvdh/laravel-dompdf 3 |
| Frontend | Blade, Tailwind CSS, Alpine.js |
| Grafik | Chart.js 4 |
| Database | SQLite (default) / MySQL |
| Queue | Database driver |
| Testing | PHPUnit 11 |

---

## Kebutuhan Sistem

- PHP **8.2+** dengan ekstensi `pdo_sqlite` (default) atau `pdo_mysql`, `mbstring`, `openssl`, `dom`, `fileinfo`.
- Composer 2.
- Node.js 18+ dan npm (untuk build asset).
- Tidak wajib Redis — queue, cache, dan session memakai database.

> Catatan: proyek ini dikembangkan dengan PHP 8.5 dan Laravel 12.7.

---

## Instalasi

### 1. Clone / salin proyek

```bash
cd "C:\laragon\www\aplikasi kategori Manajemen Bisnis & Keuangan berbasis"
```

### 2. Install dependensi

```bash
composer install
npm install
```

### 3. Siapkan environment

```bash
copy .env.example .env
php artisan key:generate
```

Jika memakai SQLite (default), buat file database:

```powershell
New-Item -ItemType File -Force database\database.sqlite
```

Pastikan `.env` berisi:

```env
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
MAIL_MAILER=log
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta
```

### 4. Migrasi & data demo

```bash
php artisan migrate --seed
php artisan storage:link
```

Perintah di atas membuat role, akun demo, 14 produk, 8 pelanggan, serta riwayat penjualan 6 bulan.

### 5. Build asset

```bash
npm run build
```

Jika folder proyek mengandung karakter `&` (seperti folder ini) dan `npm run build` gagal di PowerShell, jalankan Vite langsung:

```bash
node node_modules/vite/bin/vite.js build
```

Untuk pengembangan:

```bash
node node_modules/vite/bin/vite.js
```

### 6. Jalankan aplikasi

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### 7. Jalankan queue worker (opsional, untuk email stok menipis)

```bash
php artisan queue:work --queue=notifications,default
```

Tanpa worker, notifikasi database tetap dibuat saat job dijalankan secara sinkron? **Tidak** — job tetap masuk antrean. Jalankan worker agar email dan notifikasi database terkirim. Untuk pengembangan cepat, set `QUEUE_CONNECTION=sync` di `.env`.

---

## Akun Demo

| Role | Email | Password | Akses |
|---|---|---|---|
| Owner | `owner@demo.com` | `password` | Semua modul |
| Kasir | `kasir@demo.com` | `password` | POS, transaksi, pelanggan |
| Staff Gudang | `gudang@demo.com` | `password` | Produk, kategori, stok |

Data demo menggunakan tenant **Kopi Senja Nusantara** dengan tarif pajak 11%.

---

## Matriks Hak Akses

| Modul / Aksi | Owner | Kasir | Gudang |
|---|:---:|:---:|:---:|
| Dashboard | ✅ | ✅ | ✅ |
| POS & transaksi | ✅ | ✅ | ❌ |
| Pelanggan | ✅ | ✅ | ❌ |
| Produk & kategori | ✅ | ❌ | ✅ |
| Pergerakan stok | ✅ | ❌ | ✅ |
| Void transaksi | ✅ | ❌ | ❌ |
| Kas & keuangan | ✅ | ❌ | ❌ |
| Laporan & ekspor | ✅ | ❌ | ❌ |
| Tim & akses | ✅ | ❌ | ❌ |
| Pengaturan bisnis | ✅ | ❌ | ❌ |
| Profil sendiri | ✅ | ✅ | ✅ |

Registrasi publik selalu membuat akun **owner** untuk tenant baru. Anggota tim ditambahkan oleh owner melalui menu **Tim & Akses**.

---

## Alur Penting

### Transaksi POS
1. Buka **Kasir / POS**.
2. Cari produk atau scan barcode/SKU.
3. Atur jumlah, diskon, pelanggan, metode bayar, dan nominal bayar.
4. Simpan — sistem membuat invoice, detail transaksi, mengurangi stok, mencatat `stock_log`, dan mengirim job notifikasi jika stok menipis.
5. Invoice PDF dapat diunduh dari halaman detail transaksi.

### Stok Menipis
- Setiap produk memiliki `min_stock_alert`.
- Saat stok ≤ batas minimum setelah penjualan atau pergerakan stok, `SendLowStockNotificationJob` dikirim ke antrean `notifications`.
- Job mengirim notifikasi ke semua owner aktif dan mencatat `stock_alerts`.
- Notifikasi yang sama tidak dikirim ulang dalam 6 jam.

### Laporan
- Pilih rentang tanggal pada menu **Laporan**.
- Ringkasan laba rugi dihitung dari penjualan selesai, HPP detail transaksi, pemasukan lain, dan pengeluaran.
- Tombol **Ekspor CSV** mengunduh seluruh transaksi selesai pada rentang tersebut.

---

## Beralih ke MySQL

1. Buat database, misalnya `kasirin`.
2. Ubah `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasirin
DB_USERNAME=root
DB_PASSWORD=
```

3. Jalankan ulang migrasi dan seeder:

```bash
php artisan config:clear
php artisan migrate:fresh --seed
```

Semua query menggunakan Eloquent/Query Builder sehingga kompatibel dengan MySQL. Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis.

---

## Pengujian

```bash
php artisan test
```

Cakupan test saat ini (40 test):
- Registrasi tenant baru + role owner + kategori default.
- Isolasi data antar-tenant dan pembatasan role.
- Pembuatan penjualan, perhitungan pajak/diskon, pengurangan stok, dan nomor invoice.
- Void transaksi dan pengembalian stok.
- Job notifikasi stok menipis, anti-duplikasi, dan pencatatan `stock_alerts`.
- Autentikasi, profil, dan reset password bawaan Breeze.

Database test memakai SQLite in-memory (`phpunit.xml`), sehingga tidak mengganggu data demo.

---

## Struktur Proyek Penting

```
app/
├── Http/Controllers/        # Dashboard, POS, produk, stok, keuangan, laporan, tim, pengaturan
├── Jobs/                    # SendLowStockNotificationJob
├── Models/Concerns/         # BelongsToTenant (global scope multi-tenant)
├── Notifications/           # LowStockNotification (mail + database)
├── Policies/                # TransactionPolicy
├── Services/                # SaleService, StockService, InvoiceNumberService
└── Support/helpers.php      # rupiah(), id_date(), role_label(), month_name()

database/
├── migrations/              # Skema tenant, produk, transaksi, stok, notifikasi, permission
└── seeders/                 # RoleSeeder, DemoSeeder

resources/
├── css/app.css              # Design system (card, btn, input, badge, table)
├── js/app.js                # Alpine + Chart.js
└── views/
    ├── layouts/             # Layout sidebar & guest
    ├── components/          # Icon, badge, stat-card, alert, empty-state, dll.
    ├── pos/                 # Antarmuka kasir
    ├── products/ customers/ # CRUD
    ├── stock/               # Riwayat & form pergerakan stok
    ├── finance/ reports/    # Keuangan & laporan
    ├── team/ settings/      # Tim & pengaturan bisnis
    └── pdf/invoice.blade.php
```

---

## Perintah Berguna

```bash
# Bersihkan cache
php artisan optimize:clear

# Migrasi ulang + data demo (menghapus data yang ada)
php artisan migrate:fresh --seed

# Build asset produksi
node node_modules/vite/bin/vite.js build

# Jalankan worker notifikasi
php artisan queue:work --queue=notifications,default

# Format kode PHP
vendor/bin/pint
```

---

## Troubleshooting

**`npm run build` gagal karena path mengandung `&`**  
Gunakan pemanggilan Vite langsung seperti di atas, atau pindahkan proyek ke path tanpa `&`.

**Logo bisnis tidak muncul**  
Jalankan `php artisan storage:link` dan pastikan `APP_URL` sesuai.

**Email notifikasi tidak terkirim**  
`MAIL_MAILER=log` hanya menulis email ke `storage/logs/laravel.log`. Untuk pengiriman nyata, atur SMTP dan jalankan queue worker.

**Notifikasi tidak muncul**  
Pastikan worker berjalan (`php artisan queue:work --queue=notifications,default`) atau set `QUEUE_CONNECTION=sync` untuk pengembangan.

**Halaman 403 saat mengakses modul**  
Itu sesuai role. Login sebagai owner untuk mengakses keuangan, laporan, tim, dan pengaturan.

**PHP mengeluarkan peringatan `session.sid_length`**  
Peringatan startup dari konfigurasi PHP 8.5 bersifat non-fatal dan tidak memengaruhi aplikasi.

---

## Lisensi

Proyek ini menggunakan lisensi MIT. Silakan gunakan dan kembangkan sesuai kebutuhan bisnis Anda.
