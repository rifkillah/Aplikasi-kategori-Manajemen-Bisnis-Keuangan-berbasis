# Deploy Kasirin ke Vercel (gratis)

Aplikasi ini adalah Laravel monolith (PHP + MySQL + queue + upload file), jadi ada
beberapa hal yang perlu disesuaikan agar bisa jalan di Vercel:

| Kebutuhan | Di lokal | Di Vercel |
| --- | --- | --- |
| Runtime | PHP Laragon | **Container** (Docker + FrankenPHP) |
| Database | MySQL `kasirin` | **Database eksternal** (Neon Postgres / Aiven MySQL) |
| Queue | `queue:work` | `QUEUE_CONNECTION=sync` (dijalankan saat request) |
| Session/cache | database | database (tetap, karena DB eksternal) |
| Upload logo | `storage/app/public` | sementara (container ephemeral) |

> Runtime `vercel-php` yang lama sudah **diarsipkan** oleh pembuatnya (29 Okt 2026).
> Karena itu repo ini sekarang memakai jalur resmi Vercel: **Container Runtime**.

## 1. Siapkan database gratis

### Opsi A — Neon Postgres (paling mudah, direkomendasikan)

1. Buka **Vercel → Marketplace → Neon**, lalu hubungkan ke project.
2. Neon otomatis membuat database dan mengisi environment variable (`DATABASE_URL`, `PGHOST`, dll).
3. Salin nilai `PGHOST`, `PGDATABASE`, `PGUSER`, `PGPASSWORD` ke variabel `DB_*` di langkah 2.

### Opsi B — Aiven MySQL (kalau ingin tetap MySQL)

1. Buka **Vercel → Marketplace → Aiven for MySQL** (paket Free).
2. Salin host, port, database, user, password ke variabel `DB_*`.
3. Aiven mewajibkan SSL. Unduh `ca.pem` dari dashboard Aiven, taruh di `docker/ca.pem`
   di repo, lalu tambahkan env `MYSQL_ATTR_SSL_CA=/app/docker/ca.pem`
   dan pastikan file tersebut ikut ter-copy ke image (tidak ada di `.dockerignore`).

## 2. Isi Environment Variables di Vercel

Buka **Project → Settings → Environment Variables**, lalu isi (lihat `.env.vercel.example`):

```
APP_NAME=Kasirin
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...            # generate: php artisan key:generate --show
APP_URL=https://nama-project.vercel.app
LOG_CHANNEL=stderr

DB_CONNECTION=pgsql           # atau mysql untuk Aiven
DB_HOST=...
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
DB_SSLMODE=require

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
MAIL_MAILER=log
```

## 3. Migrasi + isi data demo

Jalankan dari komputer lokal, langsung ke database eksternal. PHP Laragon sudah punya
`php_pdo_pgsql.dll`, tinggal diaktifkan saat perintah dijalankan.

**PowerShell (Neon Postgres):**

```powershell
$env:DB_CONNECTION = 'pgsql'
$env:DB_URL = 'postgresql://USER:PASSWORD@HOST/DBNAME?sslmode=require'

C:\laragon\bin\php\php-8.5.12-1-Win32-vs17-x64_2\php.exe `
  -d extension=pdo_pgsql artisan migrate --force --seed
```

**PowerShell (Aiven MySQL):**

```powershell
$env:DB_CONNECTION = 'mysql'
$env:DB_HOST = '...'
$env:DB_PORT = '...'
$env:DB_DATABASE = '...'
$env:DB_USERNAME = '...'
$env:DB_PASSWORD = '...'

C:\laragon\bin\php\php-8.5.12-1-Win32-vs17-x64_2\php.exe artisan migrate --force --seed
```

`--seed` akan membuat 3 akun demo, 14 produk, dan riwayat transaksi.

## 4. Deploy

### Lewat GitHub (otomatis)

1. Import repo `rifkillah/Aplikasi-kategori-Manajemen-Bisnis-Keuangan-berbasis` di Vercel.
2. Pastikan **Framework Preset = Container** (Settings → Build & Development).
   Kalau project sudah ada dan masih terdeteksi sebagai "Other":
   ```bash
   vercel project update --framework container --yes
   ```
3. Push ke branch `main` → Vercel membangun image dari `Dockerfile`.

### Lewat CLI

```bash
npm i -g vercel
vercel login
vercel link            # pilih tim bismillah-bisa dan project-nya
vercel deploy --prod
```

## 5. Cek setelah deploy

- `https://<project>.vercel.app/up` → health check Laravel (harus 200).
- Buka `/login` → masuk dengan `owner@demo.com` / `password`.
- Cek **Vercel → Deployments → Runtime Logs** kalau ada error 500/502.

## Catatan penting

- **Jangan** menjalankan `php artisan config:cache` di dalam image. Konfigurasi harus
  dibaca saat runtime supaya environment variable Vercel terbaca.
- **Queue**: `QUEUE_CONNECTION=sync` berarti job notifikasi stok rendah dieksekusi
  langsung saat transaksi. Tidak ada worker terpisah di Vercel.
- **Upload logo** tersimpan sementara di container. Untuk permanen, gunakan Vercel Blob
  atau S3 lalu set `FILESYSTEM_DISK=s3`.
- **Preview deployment** punya URL berbeda; `APP_URL` sebaiknya diisi domain produksi.
  URL aset tetap benar karena `trustProxies` sudah diaktifkan di `bootstrap/app.php`.
- Image container disimpan di Vercel Container Registry (Hobby: 10 repo, 50 image,
  total 15 GB). Biaya storage $0.10/GB.
