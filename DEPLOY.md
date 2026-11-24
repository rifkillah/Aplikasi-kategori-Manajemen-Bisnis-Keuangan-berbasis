# Deploy & Hosting Kasirin

Panduan ini untuk hosting **mandiri** (shared hosting/cPanel, VPS, atau Docker) dengan database **MySQL/MariaDB** yang bisa dibuka lewat **phpMyAdmin**.

> Ringkasnya: **bisa**. Aplikasi memakai MySQL standar, jadi selama hosting dan phpMyAdmin menunjuk server + database yang sama, data yang ditulis aplikasi langsung terlihat di phpMyAdmin (dan sebaliknya). phpMyAdmin hanyalah "jendela" ke database, bukan database terpisah.

---

## 1. Pilih jenis hosting

| Pilihan | Cocok untuk | Catatan penting |
|---|---|---|
| **Shared hosting / cPanel** | UMKM, tim kecil | Paling mudah karena phpMyAdmin sudah tersedia. **Wajib cek PHP 8.6** (banyak hosting masih 8.2/8.3). |
| **VPS** (Ubuntu + Nginx/Apache + MySQL) | Trafik lebih besar, kontrol penuh | Perlu pasang PHP 8.6, MySQL, SSL, dan phpMyAdmin sendiri. |
| **Docker / platform container** | Deploy berulang | `Dockerfile` sudah tersedia (FrankenPHP). Database tetap MySQL eksternal; phpMyAdmin bisa dijalankan sebagai container terpisah. |

**Vercel tidak dipakai lagi** untuk proyek ini. Vercel bukan host PHP/MySQL biasa, filesystem-nya sementara, dan tidak menyediakan phpMyAdmin — sehingga tidak cocok dengan kebutuhan sinkronisasi database seperti ini.

### Syarat minimum
- PHP **8.6** dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `zip`, `intl`.
- MySQL **8.0+** atau MariaDB **10.6+** (utf8mb4).
- HTTPS (Let's Encrypt/cPanel AutoSSL).
- Document root mengarah ke folder **`public/`**.

> Jika hosting Anda belum menyediakan PHP 8.6, aplikasi tidak akan jalan (composer.json mensyaratkan `^8.6`). Minta provider mengaktifkannya, atau gunakan VPS.

---

## 2. Sinkronisasi database dengan phpMyAdmin

### Konsep
Ada dua skenario "sinkron":

1. **Hosting + phpMyAdmin di server yang sama** (paling umum di cPanel).
   - Buat database di cPanel, import data, isi `.env`, selesai.
   - Aplikasi dan phpMyAdmin otomatis melihat data yang sama secara real-time.
2. **Database remote** (MySQL di server lain).
   - Aktifkan **Remote MySQL** di cPanel dan izinkan IP server aplikasi.
   - Isi `DB_HOST` dengan hostname database, bukan `localhost`.
   - Pastikan firewall database mengizinkan IP tersebut. Tanpa ini, koneksi akan gagal.

### Langkah A — Ekspor database lokal (phpMyAdmin Laragon)
1. Buka `http://localhost/phpmyadmin`.
2. Pilih database `kasirin` → tab **Export**.
3. Pilih **Custom** → format **SQL**.
4. Centang **Structure and data**, aktifkan **Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT**.
5. Simpan file, misalnya `kasirin.sql`.

> Untuk database besar, gunakan `mysqldump` agar lebih cepat:
> ```bash
> mysqldump -u root kasirin > kasirin.sql
> ```

### Langkah B — Impor ke hosting
1. Di cPanel, buka **MySQL Databases**:
   - Buat database, misalnya `user_kasirin`.
   - Buat user database + password kuat.
   - Tambahkan user ke database dengan hak **ALL PRIVILEGES**.
2. Buka **phpMyAdmin** hosting → pilih database tersebut → tab **Import**.
3. Upload `kasirin.sql` → **Go**.
4. Pastikan tabel seperti `tenants`, `users`, `products`, `transactions`, `migrations` muncul.

### Langkah C — Hubungkan aplikasi
Edit `.env` di server:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=localhost          # atau hostname remote MySQL dari cPanel
DB_PORT=3306
DB_DATABASE=user_kasirin
DB_USERNAME=user_kasirin
DB_PASSWORD=password-kuat

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Lalu:

```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

`migrate --force` aman dijalankan setelah import: migrasi yang sudah tercatat di tabel `migrations` akan dilewati.

### Catatan penting setelah import
- **Kosongkan sesi & cache lokal** agar tidak ada sesi "hantu" dari komputer Anda:
  ```sql
  TRUNCATE TABLE sessions;
  TRUNCATE TABLE cache;
  TRUNCATE TABLE cache_locks;
  TRUNCATE TABLE jobs;
  TRUNCATE TABLE job_batches;
  TRUNCATE TABLE failed_jobs;
  ```
- **Ganti/hapus akun demo** (`owner@demo.com`, `kasir@demo.com`, `gudang@demo.com` dengan password `password`) sebelum go-live.
- Jika tidak ingin membawa data contoh, impor **struktur saja**, lalu jalankan `php artisan migrate --force` dan buat akun lewat halaman register.
- Zona waktu: `APP_TIMEZONE=Asia/Jakarta` sudah default. Pastikan MySQL memakai `utf8mb4` dan zona waktu server konsisten.

### Apakah data akan "tersinkron" dua arah?
Ya, selama aplikasi dan phpMyAdmin memakai **database yang sama**. Setiap transaksi POS, produk, atau pengeluaran yang ditulis aplikasi langsung muncul di phpMyAdmin. Perubahan manual di phpMyAdmin juga langsung terbaca aplikasi — tetapi hati-hati: mengubah data keuangan secara manual bisa merusak konsistensi stok/relasi. Utamakan lewat aplikasi.

---

## 3. Upload proyek ke shared hosting (cPanel)

1. **Build asset di lokal** (hosting biasanya tidak punya Node.js):
   ```bash
   npm ci
   npm run build
   ```
   Folder `public/build/` ikut diunggah.
2. **Composer di server**:
   - Jika tersedia: jalankan `composer install --no-dev --optimize-autoloader`.
   - Jika tidak: jalankan `composer install --no-dev --optimize-autoloader` di lokal, lalu unggah folder `vendor/`. Pastikan versi PHP lokal dan hosting sama-sama 8.6.
3. Unggah seluruh proyek ke luar `public_html`, misalnya `/home/user/kasirin`.
4. Arahkan domain/subdomain ke folder `kasirin/public`:
   - cPanel → **Domains** → ubah **Document Root** ke `/home/user/kasirin/public`.
   - Jika tidak bisa, pindahkan isi `public/` ke `public_html/`, lalu sesuaikan `index.php` agar menunjuk `../kasirin/vendor/autoload.php` dan `../kasirin/bootstrap/app.php`. Jangan pernah menaruh `.env` di dalam `public_html`.
5. Pastikan permission: folder `755`, file `644`; `storage/` dan `bootstrap/cache/` writable (`775` bila perlu).
6. Buat symlink storage:
   ```bash
   php artisan storage:link
   ```
   Jika symlink diblokir hosting, buat folder `public/storage` dan arahkan ke `../storage/app/public` lewat File Manager, atau gunakan S3.
7. Jalankan:
   ```bash
   php artisan key:generate --force   # hanya jika APP_KEY masih kosong
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Queue & cron
Notifikasi stok menipis memakai queue. Di shared hosting tanpa worker permanen:

```bash
# jalankan tiap menit lewat cron
* * * * * cd /home/user/kasirin && php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

Alternatif paling sederhana: set `QUEUE_CONNECTION=sync` di `.env` (email dikirim saat transaksi, tanpa worker). Untuk VPS, gunakan Supervisor.

---

## 4. VPS (ringkas)

```bash
sudo apt install nginx mysql-server php8.6-fpm php8.6-mysql php8.6-mbstring \
  php8.6-xml php8.6-curl php8.6-zip php8.6-gd php8.6-intl php8.6-bcmath
```

- Nginx `root /var/www/kasirin/public;`
- `try_files $uri $uri/ /index.php?$query_string;`
- Pasang phpMyAdmin (batasi akses dengan basic auth/IP allowlist, jangan diekspos publik tanpa proteksi).
- SSL: `certbot --nginx -d domain-anda.com`.
- Set `TRUSTED_PROXIES` hanya jika ada reverse proxy/Cloudflare di depan Nginx.

---

## 5. Docker

```bash
docker build -t kasirin .
docker run -d --name kasirin -p 8080:80 \
  -e APP_KEY=base64:... \
  -e APP_URL=https://domain-anda.com \
  -e DB_HOST=host-mysql -e DB_DATABASE=kasirin \
  -e DB_USERNAME=kasirin -e DB_PASSWORD=rahasia \
  kasirin
```

- Database **tidak** dijalankan di dalam container aplikasi; gunakan MySQL eksternal (container `mysql` atau server terpisah) agar data persisten.
- Upload pengguna (`storage/app/public`) juga harus volume persisten atau S3, jika tidak akan hilang saat container dibuat ulang.
- phpMyAdmin bisa dijalankan sebagai service terpisah yang terhubung ke MySQL yang sama.

---

## 6. Checklist setelah deploy

- [ ] `https://domain-anda.com/up` mengembalikan 200.
- [ ] Login, POS, PDF, export CSV, dan notifikasi berjalan.
- [ ] Data yang dibuat di aplikasi muncul di phpMyAdmin.
- [ ] `APP_DEBUG=false`, `APP_ENV=production`, `APP_URL=https://...`.
- [ ] `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `LOG_LEVEL=warning`.
- [ ] Akun demo sudah dihapus/diganti.
- [ ] Backup database harian (cPanel backup atau `mysqldump` otomatis).
- [ ] phpMyAdmin tidak dapat diakses publik tanpa proteksi tambahan.

Lihat juga [SECURITY.md](SECURITY.md) untuk kontrol keamanan dan risiko sisa.
