# Setup Guide — Trimexas

## Sistem Pendukung Keputusan Beasiswa Fuzzy Tsukamoto

**Date:** 2026-05-20
**Audience:** Anggota tim Kelompok 4, dosen pengampu, evaluator.

---

## 1. Prerequisites

Pastikan tools berikut terinstal di mesin pengembangan:

| Tool             | Versi Minimum | Cek Versi                        |
| ---------------- | ------------- | -------------------------------- |
| Docker Desktop   | 4.30+         | `docker --version`               |
| Docker Compose   | v2.20+        | `docker compose version`         |
| Git              | 2.40+         | `git --version`                  |
| Node.js (opsional, untuk run npm di luar Docker) | 22+ | `node --version`                 |

> **Windows users:** Pastikan WSL2 backend Docker Desktop aktif. Path proyek yang mengandung spasi (mis. `C:\my filess dudeee\...`) didukung tapi disarankan **clone ke path tanpa spasi** untuk menghindari edge case Vite/Composer.

> **macOS Apple Silicon:** Tidak perlu konfigurasi tambahan; image multi-arch.

> **Linux:** Pastikan user masuk grup `docker` agar tidak perlu `sudo`.

---

## 2. Quick Start (5 Menit)

### 2.1 Clone Repository

```bash
git clone <repo-url> trimexas
cd trimexas
```

### 2.2 Setup Environment File

```bash
cp .env.example .env
```

Edit `.env` jika perlu mengganti credentials default. Default sudah jalan untuk pengembangan lokal.

### 2.3 Build & Start Containers

```bash
docker compose up -d --build
```

Output yang diharapkan: 5 service running (`app`, `web`, `postgres`, `vite`, `mailpit`). Cek:

```bash
docker compose ps
```

### 2.4 Install Dependencies

```bash
docker compose exec app composer install
docker compose exec vite npm install
```

### 2.5 Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

### 2.6 Migrate Database & Seed

```bash
docker compose exec app php artisan migrate --seed
```

Seeder akan populate:
- Default admin user
- 5 kriteria + himpunan fuzzy default
- 75 rule (sesuai `KnowledgeBase_RuleSpec.md`)
- Output thresholds default
- 50 demo students (untuk testing)

### 2.7 Akses Aplikasi

| Service         | URL                       |
| --------------- | ------------------------- |
| Aplikasi web    | http://localhost          |
| Mailpit (email) | http://localhost:8025     |
| Vite HMR        | http://localhost:5173     |

### 2.8 Default Admin Credentials

| Field       | Value                       |
| ----------- | --------------------------- |
| Email       | `admin@trimexas.local`      |
| Password    | `admin12345`                |

> Wajib ganti password setelah login pertama.

---

## 3. Detail Setup

### 3.1 Struktur Docker Compose

`docker-compose.yml` mendefinisikan service:

| Service   | Image                  | Port                | Purpose                         |
| --------- | ---------------------- | ------------------- | ------------------------------- |
| `app`     | Custom (PHP 8.3-FPM)   | -                   | Laravel + Composer              |
| `web`     | `nginx:alpine`         | 80 → 80             | HTTP server, proxy ke PHP-FPM   |
| `postgres`| `postgres:16-alpine`   | 5432 → 5432         | Database                        |
| `vite`    | `node:22-alpine`       | 5173 → 5173         | Vite dev server (HMR)           |
| `mailpit` | `axllent/mailpit:latest`| 1025, 8025 → 1025, 8025 | SMTP catcher + UI         |

### 3.2 Environment Variables

File `.env.example` berisi konfigurasi lengkap. Variable kunci:

```env
APP_NAME=Trimexas
APP_ENV=local
APP_KEY=                      # diisi oleh artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=trimexas
DB_USERNAME=postgres
DB_PASSWORD=secret

QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=30

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@trimexas.local"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

### 3.3 Switch Mail ke Gmail SMTP (untuk Demo)

Saat presentasi ke dosen, switch dari Mailpit ke Gmail SMTP supaya email beneran masuk inbox.

**Langkah:**

1. Aktifkan 2-Step Verification di akun Google.
2. Generate App Password: https://myaccount.google.com/apppasswords (pilih "Mail" → "Other").
3. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"   # App Password 16-digit
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

4. Restart container:

```bash
docker compose restart app
```

5. Test via Tinker:

```bash
docker compose exec app php artisan tinker
> Mail::raw('Test dari Trimexas', fn($m) => $m->to('target@example.com')->subject('Test'));
```

---

## 4. Workflow Pengembangan

### 4.1 Hot Reload Frontend

Vite dev server berjalan di container `vite` dengan HMR aktif. Saat edit file di `resources/js/`, browser auto-refresh.

Jika tidak auto-refresh:

```bash
docker compose restart vite
```

### 4.2 Menjalankan Migration Tambahan

```bash
docker compose exec app php artisan make:migration create_xxx_table
# edit file migration
docker compose exec app php artisan migrate
```

### 4.3 Reset Database (Development Only)

```bash
docker compose exec app php artisan migrate:fresh --seed
```

> **Hati-hati:** Perintah ini **drop all tables** lalu re-migrate + re-seed. Jangan dijalankan di production.

### 4.4 Menjalankan Queue Worker Manual

Default sistem pakai self-spawning worker (lihat PRD §9.2). Untuk debugging, bisa jalankan worker manual:

```bash
docker compose exec app php artisan queue:work --queue=seleksi --verbose
```

### 4.4.1 Fallback Windows (tanpa Docker)

Jika `Symfony\Process` gagal spawn worker (mis. native Windows tanpa WSL), jalankan worker manual sebagai background:

```powershell
# Powershell
Start-Process -NoNewWindow -FilePath php -ArgumentList "artisan queue:work --queue=seleksi,notifications --tries=3"
```

Atau via task scheduler / NSSM (`nssm install trimexas-queue php artisan queue:work --queue=seleksi`).

### 4.5 Menjalankan Test Suite (Pest)

Seluruh test berjalan di SQLite in-memory; tidak butuh PostgreSQL.

```bash
vendor/bin/pest --no-coverage                       # full suite (104 tests)
vendor/bin/pest tests/Feature/Domain --no-coverage   # 5 manual fuzzy cases
vendor/bin/pest tests/Feature/Privacy --no-coverage  # privacy assertions
SKIP_PERF_TEST=false vendor/bin/pest tests/Feature/Performance --no-coverage   # 1.000 candidate profiling
```

Pest plugin Laravel (`pest-plugin-laravel ^4.1`) sudah aktif lewat `tests/Pest.php`.

### 4.5 Akses Database (CLI)

```bash
docker compose exec postgres psql -U postgres -d trimexas
```

Atau pakai GUI client (DBeaver, TablePlus, pgAdmin):

| Field    | Value          |
| -------- | -------------- |
| Host     | localhost      |
| Port     | 5432           |
| Database | trimexas       |
| User     | postgres       |
| Password | secret (default) |

### 4.6 Akses Mailpit UI

Buka http://localhost:8025 di browser. Semua email yang dikirim aplikasi (saat `MAIL_HOST=mailpit`) muncul di sini.

---

## 5. Testing

### 5.1 Run Unit Tests

```bash
docker compose exec app php artisan test
```

Atau spesifik untuk domain layer:

```bash
docker compose exec app php artisan test --filter=Domain
```

Atau pakai Pest langsung:

```bash
docker compose exec app ./vendor/bin/pest
docker compose exec app ./vendor/bin/pest --coverage
```

### 5.2 Run Code Quality Checks

```bash
# PHP formatting
docker compose exec app ./vendor/bin/pint

# JS/Vue lint
docker compose exec vite npm run lint

# JS/Vue format check
docker compose exec vite npm run format:check
```

Auto-fix:

```bash
docker compose exec vite npm run format
```

---

## 6. Troubleshooting

### 6.1 `docker compose up` Gagal Build

**Gejala:** Error saat build image PHP atau Node.

**Solusi:**

```bash
docker compose down -v
docker system prune -f
docker compose up -d --build --force-recreate
```

### 6.2 Database Connection Refused

**Gejala:** `SQLSTATE[08006] could not translate host name "postgres"`.

**Cek:**
- Pastikan service `postgres` running: `docker compose ps`.
- Restart `app` setelah `postgres` healthy: `docker compose restart app`.
- Cek `.env`: `DB_HOST=postgres` (bukan `localhost`).

### 6.3 Vite HMR Tidak Bekerja di Windows

**Gejala:** Edit file Vue tidak refresh browser.

**Solusi:**

Edit `vite.config.js`, tambahkan `usePolling`:

```ts
server: {
  watch: {
    usePolling: true,
    interval: 1000,
  },
},
```

### 6.4 Permission Denied di Storage

**Gejala:** `Permission denied` saat menulis log atau cache.

**Solusi:**

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### 6.5 Queue Worker Tidak Spawn

**Gejala:** Klik "Run Selection" tapi job tidak diproses.

**Cek:**

```bash
# Cek pid file
docker compose exec app cat storage/app/queue-worker.pid

# Cek apakah ada process queue:work
docker compose exec app ps aux | grep queue:work

# Cek log
docker compose exec app tail -f storage/logs/laravel.log
```

Jika worker tidak spawn, jalankan manual sebagai workaround:

```bash
docker compose exec -d app php artisan queue:work --queue=seleksi --stop-when-empty
```

### 6.6 Port 80 / 5432 / 8025 Sudah Dipakai

**Gejala:** `bind: address already in use`.

**Solusi:** Edit `docker-compose.yml`, ubah host port:

```yaml
ports:
  - "8080:80"      # web (akses via localhost:8080)
  - "5433:5432"    # postgres
  - "8026:8025"    # mailpit UI
```

Restart: `docker compose down && docker compose up -d`.

### 6.7 Path Mengandung Spasi (Windows)

**Gejala:** Composer atau npm error karena path proyek mengandung spasi (mis. `C:\my filess dudeee\...`).

**Solusi rekomendasi:** Pindahkan repo ke path tanpa spasi, mis. `C:\dev\trimexas`.

---

## 7. Update Dependencies

### 7.1 Composer

```bash
docker compose exec app composer update
docker compose exec app composer outdated
```

### 7.2 NPM

```bash
docker compose exec vite npm update
docker compose exec vite npm outdated
```

> Setelah update package versi mayor, **selalu** run unit test untuk memastikan tidak ada breaking change.

---

## 8. Stop & Cleanup

### 8.1 Stop Containers (data dipertahankan)

```bash
docker compose stop
```

### 8.2 Stop & Remove Containers (data dipertahankan)

```bash
docker compose down
```

### 8.3 Stop & Remove Containers + Volume Database (data terhapus)

```bash
docker compose down -v
```

> Setelah `down -v`, semua data DB hilang. Re-run setup dari §2 untuk start ulang.

---

## 9. Production Deployment (Catatan Singkat)

> Production deployment **bukan** scope MVP. Catatan berikut hanya untuk referensi pasca-MVP.

Untuk production:

1. Set `APP_ENV=production` dan `APP_DEBUG=false` di `.env`.
2. Build asset frontend: `npm run build`.
3. Cache config & route: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
4. Jalankan queue worker sebagai service via Supervisor (Linux) atau Task Scheduler (Windows).
5. Setup HTTPS via reverse proxy (Caddy / Traefik / Nginx + Let's Encrypt).
6. Backup database otomatis (mis. `pg_dump` cron).
7. Monitor uptime & error (mis. Sentry, Bugsnag).

---

## 10. Bantuan & Kontak

- **Repo issue tracker:** lapor bug atau pertanyaan setup di GitHub Issues.
- **Tim:** Kelompok 4 — Praktikum Artificial Intelligence.
- **Dokumen terkait:**
  - `docs/PRD_Final.md` — spesifikasi produk lengkap.
  - `docs/User_Manual.md` — panduan penggunaan sistem.
  - `docs/KnowledgeBase_RuleSpec.md` — rule base fuzzy.
  - `docs/Test_Report.md` — validasi metodologi.

---

*— Akhir Setup Guide —*
