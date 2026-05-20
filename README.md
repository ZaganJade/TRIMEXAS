<h1 align="center">Trimexas</h1>

<p align="center">
  <strong>Sistem Pendukung Keputusan Seleksi Penerima Beasiswa</strong><br/>
  Triv Foundation × MEXC Foundation — Metode Fuzzy Tsukamoto
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white">
  <img alt="Inertia" src="https://img.shields.io/badge/Inertia-2.x-9553E9">
  <img alt="Vue" src="https://img.shields.io/badge/Vue-3.5-4FC08D?logo=vue.js&logoColor=white">
  <img alt="Tailwind" src="https://img.shields.io/badge/Tailwind-4.x-38BDF8?logo=tailwindcss&logoColor=white">
  <img alt="PostgreSQL" src="https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white">
  <img alt="Status" src="https://img.shields.io/badge/Status-MVP%20%E2%80%94%20M1%20in%20progress-yellow">
</p>

---

## Tentang Proyek

Trimexas (codename **Tri**v × **MEX**C **A**cademic **S**election) adalah aplikasi web Sistem Pendukung Keputusan (SPK) untuk membantu pengelola beasiswa Triv Foundation × MEXC Foundation menyeleksi mahasiswa secara objektif dan dapat diaudit. Sistem mengimplementasikan metode inferensi **Fuzzy Tsukamoto** untuk mengolah lima variabel non-deterministik menjadi skor kelayakan 0–100 yang dikategorikan **Tidak Layak**, **Dipertimbangkan**, atau **Layak**.

Proyek ini dikerjakan sebagai tugas **Praktikum Artificial Intelligence Semester 4 — Kelompok 4** dengan target MVP demo dalam horizon 5 minggu.

### Variabel Inferensi

| Kriteria                     | Domain | Himpunan Fuzzy             |
| ---------------------------- | ------ | -------------------------- |
| IPK                          | 3,00 – 4,00 | Cukup / Baik / Sangat Baik |
| Penghasilan Orang Tua (juta) | 1 – 10 | Rendah / Menengah / Tinggi |
| Prestasi Akademis            | 0 – 50 | Sedikit / Sedang / Banyak  |
| Prestasi Non-Akademis        | 0 – 50 | Sedikit / Sedang / Banyak  |
| Tanggungan Keluarga          | 1 – 8  | Sedikit / Sedang / Banyak  |

Pipeline deterministik: **Eligibility Gate → Fuzzifikasi → Inferensi Tsukamoto (AND = MIN) → Defuzzifikasi (Weighted Average)** dengan rule base 75 rule (lihat [`docs/KnowledgeBase_RuleSpec.md`](docs/KnowledgeBase_RuleSpec.md)).

---

## Fitur Utama (MVP)

- **Auth multi-role** — Admin & Mahasiswa, dengan self-registration mahasiswa dan workflow approval admin (Pending → Approved / Rejected).
- **Manajemen mahasiswa & prestasi** — CRUD admin, edit profil mandiri mahasiswa, maksimum 5 entri prestasi total dengan agregat per kategori di-cap 50.
- **Konfigurasi kriteria fuzzy** — Parameter himpunan (a, b, c) editable via UI dengan validasi monotonik, threshold output editable, rule base hard-coded via seeder.
- **Mesin Fuzzy Tsukamoto** — Domain layer murni PHP (framework-agnostic), unit-testable tanpa boot Laravel.
- **Eksekusi seleksi async** — Queue `database` dengan **self-spawning worker** (`queue:work --stop-when-empty` via `Symfony\Process`); chunking 50 kandidat per job; snapshot parameter, rules, dan thresholds per batch.
- **Ranking dua-rute** — Admin (full + audit), publik mahasiswa (Nama + Skor + Status saja, NIM & data sensitif tidak pernah dikirim ke frontend).
- **Audit trail per kandidat** — Input, derajat keanggotaan, α-predikat, z-rule, skor final.
- **Riwayat batch** dengan snapshot reproducible.
- **Export laporan** CSV (League CSV) dan PDF (DomPDF).
- **Notifikasi** in-app + email (Mailpit dev / Gmail SMTP demo) untuk approval mahasiswa dan completion batch.
- **Activity log** (Spatie) untuk seluruh aksi penting admin.
- **Landing page publik** target Lighthouse Performance ≥ 90 & Accessibility ≥ 90.

---

## Tech Stack

### Backend

| Layer       | Teknologi                                              |
| ----------- | ------------------------------------------------------ |
| Runtime     | PHP 8.3+                                               |
| Framework   | Laravel 13.x                                           |
| Database    | PostgreSQL 16 (kolom `jsonb` untuk snapshot & audit)   |
| Queue       | Driver `database` + self-spawning worker via Symfony Process |
| Logging     | `spatie/laravel-activitylog` ^4.10                     |
| Routing JS  | `tightenco/ziggy` ^2.0                                 |
| Test        | Pest (planned), PHPUnit 12 (current)                   |

### Frontend

| Layer      | Teknologi                                                |
| ---------- | -------------------------------------------------------- |
| UI         | Vue 3.5 + Inertia 2 (SPA-like dengan routing Laravel)    |
| Styling    | Tailwind CSS 4 (CSS-first config) + shadcn-vue + radix-vue |
| Build      | Vite 8 + `@vitejs/plugin-vue` + `@tailwindcss/vite`      |
| Ikonografi | `@lucide/vue`                                            |

### DevOps & Tooling

- **Docker Compose** — service `app` (PHP-FPM 8.3), `web` (nginx), `postgres:16`, `mailpit`, `vite`
- **Pint** — PHP formatter
- **ESLint + Prettier** + `prettier-plugin-tailwindcss` — JS/Vue formatter & linter
- **Husky + lint-staged** — pre-commit hook (Pint + ESLint + Prettier)

---

## Quick Start

> Detail lengkap (Windows fallback worker, troubleshooting, profil setup) ada di [`docs/Setup_Guide.md`](docs/Setup_Guide.md).

### Prerequisites

- Docker Desktop 4.30+
- Docker Compose v2.20+
- Git 2.40+
- Node.js 22+ (opsional, untuk run npm di luar container)

### 1. Clone & masuk folder

```bash
git clone <repo-url> trimexas
cd trimexas
```

### 2. Setup environment

```bash
cp .env.example .env
```

### 3. Build & start containers

```bash
docker compose up -d --build
docker compose ps   # pastikan 5 service running
```

### 4. Install dependencies

```bash
docker compose exec app composer install
docker compose exec vite npm install
```

### 5. Generate key, migrate, seed

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Seeder mengisi: 1 admin default, 5 kriteria + parameter himpunan default, threshold output (50, 75), dan 75 rule dari `KnowledgeBase_RuleSpec.md`.

### 6. Akses aplikasi

| Layanan        | URL                       |
| -------------- | ------------------------- |
| Aplikasi       | http://localhost          |
| Vite HMR       | http://localhost:5173     |
| Mailpit (mail) | http://localhost:8025     |

> **Windows users:** Disarankan clone ke path tanpa spasi untuk menghindari edge case Vite/Composer. Self-spawning worker punya fallback manual; lihat Setup Guide §Worker Manual Fallback.

---

## Struktur Proyek

```
.
├── app/
│   ├── Domain/                 # Pure PHP, framework-agnostic
│   │   ├── Fuzzy/              # MembershipFunctions, Fuzzifier, InferenceEngine, Defuzzifier
│   │   ├── Achievement/        # AchievementScorer (level × peringkat, cap 50)
│   │   └── Queue/              # WorkerManager (self-spawn via Symfony\Process)
│   ├── Http/Controllers/       # Admin/* dan Mahasiswa/* dengan privacy data shaping
│   ├── Jobs/                   # ProcessSelectionBatchJob, ProcessCandidateChunkJob
│   └── Models/                 # Student, StudentAchievement, Criterion, FuzzySet, Rule, …
├── database/
│   ├── migrations/             # users, students, criteria, fuzzy_sets, rules, selection_*
│   └── seeders/                # AdminSeeder, CriteriaSeeder, RuleSeeder (75 rule), …
├── resources/
│   ├── css/app.css             # Tailwind 4 entry
│   └── js/
│       ├── Pages/              # Inertia pages (Landing, Auth, Admin, Mahasiswa)
│       ├── components/ui/      # shadcn-vue base components
│       └── lib/utils.js
├── routes/
│   └── web.php                 # Public + auth + admin + mahasiswa
├── docker/
│   ├── nginx/default.conf
│   └── php/Dockerfile + php.ini
├── docs/                       # PRD, Setup Guide, User Manual, Test Report, RuleSpec
├── openspec/                   # Spec-driven workflow (changes & specs)
├── tests/                      # Feature + Unit (target: Pest)
└── docker-compose.yml
```

---

## Arsitektur Singkat

```
┌─────────────────────────────────────────────────────┐
│  Presentation Layer                                  │
│  Vue 3 + Inertia + Tailwind 4 + shadcn-vue           │
└─────────────────────────────────────────────────────┘
                          ↕ Inertia
┌─────────────────────────────────────────────────────┐
│  Application Layer (Laravel 13)                      │
│  Controllers · Form Requests · Jobs · Services       │
└─────────────────────────────────────────────────────┘
                          ↕ DTO
┌─────────────────────────────────────────────────────┐
│  Domain Layer (Pure PHP, framework-agnostic)         │
│  EligibilityChecker · Fuzzifier · InferenceEngine    │
│  Defuzzifier · AchievementScorer · WorkerManager     │
└─────────────────────────────────────────────────────┘
                          ↕ Eloquent
┌─────────────────────────────────────────────────────┐
│  Data Layer (PostgreSQL 16 + JSONB snapshot)         │
└─────────────────────────────────────────────────────┘
```

Domain layer **wajib** tidak bergantung pada `Illuminate\*` agar dapat di-unit-test tanpa boot Laravel (NFR-013). Reproducibility dijamin oleh snapshot JSONB parameter + rules + thresholds per batch.

---

## Roadmap & Status

| Milestone | Minggu | Fokus                                               | Status            |
| --------- | ------ | --------------------------------------------------- | ----------------- |
| M1        | 1      | Foundation: scaffold, Docker, landing page, schema  | 🟡 In progress (1.19 pending) |
| M2        | 2      | Domain layer Fuzzy + 5 kasus uji manual             | ⚪ Not started    |
| M3        | 3      | Criteria config, snapshot, batch async + worker     | ⚪ Not started    |
| M4        | 4      | UI mahasiswa, notifikasi, export, activity log      | ⚪ Not started    |
| M5        | 5      | Validation, Test Report, dokumentasi, demo          | ⚪ Not started    |

Detail tasks: [`openspec/changes/add-trimexas-spk-mvp/tasks.md`](openspec/changes/add-trimexas-spk-mvp/tasks.md).

### Acceptance Criteria

- ✅ `docker compose up` reproducible di Windows / macOS / Linux
- ⏳ Selisih perhitungan ≤ **0,01** vs perhitungan manual pada 5 kasus uji
- ⏳ ≤ **5 menit** untuk batch 1.000 kandidat
- ⏳ Field sensitif (`nim`, `penghasilan`, `audit_*`) tidak pernah masuk Inertia props mahasiswa
- ⏳ Lighthouse Performance ≥ 90 & Accessibility ≥ 90 pada landing page

---

## Dokumentasi

| Dokumen                                                            | Isi                                                              |
| ------------------------------------------------------------------ | ---------------------------------------------------------------- |
| [`docs/PRD_Final.md`](docs/PRD_Final.md)                           | Product Requirements Document v3.0 — visi, FR/NFR, roadmap       |
| [`docs/Setup_Guide.md`](docs/Setup_Guide.md)                       | Panduan setup detail + troubleshooting + Windows fallback        |
| [`docs/User_Manual.md`](docs/User_Manual.md)                       | Panduan pengguna admin & mahasiswa dengan screenshot             |
| [`docs/Test_Report.md`](docs/Test_Report.md)                       | Laporan validasi metodologi & hasil profiling                    |
| [`docs/KnowledgeBase_RuleSpec.md`](docs/KnowledgeBase_RuleSpec.md) | Spesifikasi 75 rule fuzzy + 5 kasus uji manual                   |
| [`openspec/`](openspec/)                                           | Spec-driven workflow: changes, specs, design decisions, tasks    |

---

## Tim Pengembang — Kelompok 4

| Nama                                  | Peran          |
| ------------------------------------- | -------------- |
| Muhammad Ikhsanudin Arsalan           | Anggota Tim    |
| Ahmad Irsyad Zahrani Nur Abdullah     | Anggota Tim    |
| Muhammad Javier Rakha Abhista         | Anggota Tim    |
| Muhammad Rizki Ibrahim                | Anggota Tim    |

**Mata Kuliah:** Praktikum Artificial Intelligence — Semester 4

---

## Lisensi

Proyek akademik. Konten internal mengikuti kebijakan kampus; framework dan dependency mengikuti lisensi masing-masing (Laravel: MIT).
