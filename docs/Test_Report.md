# Trimexas — Test Report

## Praktikum AI · Kelompok 4 · Semester 4

**Status**: Draft (M1) — akan di-update tiap milestone.

---

## 1. Acceptance Criteria #2 — Validasi Mesin Fuzzy Tsukamoto

> Selisih ≤ 0,01 vs perhitungan manual untuk 5 kasus uji.

### 1.1 Hasil Mesin (M2 baseline)

Dijalankan oleh `tests/Feature/Domain/FuzzyEngineManualCasesTest.php` dengan
seeder default (CriteriaSeeder + RuleSeeder + OutputThresholdSeeder; threshold
50/75). Skor di bawah adalah **baseline** dari mesin Tsukamoto + parameter
himpunan default — bukan perhitungan manual final.

| # | Profil                                       | Sketsa §7        | Output Sistem (M2)        | Skor   | Status |
| - | -------------------------------------------- | ---------------- | ------------------------- | ------ | ------ |
| 1 | IPK 3,9; Hasil 2 jt;  PA 30; PNA 12; Tng 5   | Layak            | Layak                     | 89,50  | ✅ match |
| 2 | IPK 3,3; Hasil 5 jt;  PA 8;  PNA 20; Tng 3   | Dipertimbangkan  | Dipertimbangkan           | 53,33  | ✅ match |
| 3 | IPK 3,7; Hasil 12 jt; PA 4;  PNA 6;  Tng 1   | Tidak Layak      | Tidak Layak               | 26,35  | ✅ match |
| 4 | IPK 3,5; Hasil 4 jt;  PA 18; PNA 10; Tng 4   | Layak            | **Dipertimbangkan**       | 63,75  | ⚠️ divergen |
| 5 | IPK 3,1; Hasil 8 jt;  PA 12; PNA 8;  Tng 2   | Dipertimbangkan  | **Tidak Layak (no fire)** | 0,00   | ⚠️ divergen |

### 1.2 Diagnosis Divergensi (Kasus #4 dan #5)

Divergensi adalah **akibat deterministik** dari snapshot rule + parameter
himpunan, bukan bug mesin. Tabel firing-rule top-3 (dari `RuleEvaluation`):

- **Kasus #4 (IPK 3,5):** μ_tinggi(IPK)=0 (a=3,6 b=3,75) sehingga rule "layak"
  ber-antecedent `ipk=tinggi` tidak fire. PA=18 berbagi α antara `sedang` (0,7)
  dan `banyak` (0,3); rule layak dengan `ipk=sedang` (R012–R015) ada tapi semua
  butuh `pa=banyak` atau `pna=banyak` dengan derajat tinggi. Yang dominan jadi
  R041/R043/R051 (`dipertimbangkan`, α=0,5, z=62,5). Skor 63,75 < threshold 75
  → kategori `dipertimbangkan`.
- **Kasus #5 (IPK 3,1):** μ_rendah(IPK)=1, μ_sedang(IPK)=0, μ_tinggi(IPK)=0.
  Rule base 75 rule **tidak punya** antecedent `ipk=rendah` (asumsi desain:
  IPK ≥ 3,25 baru bermakna). Σαᵢ = 0 → defuzzifier mengembalikan 0 (kategori
  `tidak_layak`).

### 1.3 Tindak Lanjut (Open Issues)

Kedua divergensi diangkat sebagai input untuk M5 (lihat tasks 11.5):

1. **Re-kalibrasi parameter IPK** — turunkan boundary μ_tinggi (mis. a=3,5
   b=3,7) supaya IPK 3,5 mulai mendukung rule `ipk=tinggi`; tetap di-snapshot
   per batch sehingga ranking historis tidak berubah.
2. **Tambah rule untuk `ipk=rendah`** — minimal 5–10 rule baru (misalnya saat
   PA=banyak + ekonomi=rendah) sehingga IPK 3,0–3,25 tidak menghasilkan skor 0.
3. **Validasi pakar (yayasan)** — sketsa §7 di KnowledgeBase adalah intuisi
   penulis. M5 perlu konfirmasi: kategori mana yang "benar" untuk kasus #4 dan
   #5? Setelah pakar memutuskan, perhitungan manual akan dilakukan untuk semua
   5 kasus dan diisi ke kolom "Manual" di tabel di bawah.

### 1.4 Audit ≤ 0,01 vs Perhitungan Manual (akan diisi M5)

| # | Profil                                       | Skor Sistem | Skor Manual | Selisih | Status |
| - | -------------------------------------------- | ----------- | ----------- | ------- | ------ |
| 1 | IPK 3,9; Hasil 2 jt;  PA 30; PNA 12; Tng 5   | 89,5000     | _pending M5_ | _pending_ | ⏳ |
| 2 | IPK 3,3; Hasil 5 jt;  PA 8;  PNA 20; Tng 3   | 53,3333     | _pending M5_ | _pending_ | ⏳ |
| 3 | IPK 3,7; Hasil 12 jt; PA 4;  PNA 6;  Tng 1   | 26,3542     | _pending M5_ | _pending_ | ⏳ |
| 4 | IPK 3,5; Hasil 4 jt;  PA 18; PNA 10; Tng 4   | 63,7500     | _pending M5_ | _pending_ | ⏳ |
| 5 | IPK 3,1; Hasil 8 jt;  PA 12; PNA 8;  Tng 2   | 0,0000      | _pending M5_ | _pending_ | ⏳ |

### 1.5 Reproducibility

```bash
# Re-jalankan baseline:
php artisan migrate:fresh --seed --env=testing
vendor/bin/pest tests/Feature/Domain --no-coverage
```

Test mengunci skor lewat `expectedScore` ± 0,01 sehingga regression apapun pada
mesin atau snapshot akan langsung gagal.


---

## 2. Lighthouse Audit — Landing Page (M1)

> Target: Performance ≥ 90, Accessibility ≥ 90.

Audit dijalankan via Chrome DevTools (Lighthouse panel) pada lingkungan
production-built (`npm run build && php artisan serve` atau Docker
`docker compose up`).

### Cara verifikasi lokal

```bash
# Terminal 1: serve aset production
php artisan serve

# Atau lewat Docker:
# docker compose up -d
# buka http://localhost:8080

# Lalu di Chrome DevTools:
#   1. Buka http://localhost:8000 (atau :8080)
#   2. F12 → Lighthouse → Mobile / Desktop
#   3. Categories: Performance, Accessibility, Best Practices, SEO
#   4. Klik "Analyze page load"
```

| Kategori        | Target | Hasil M1 |
| --------------- | ------ | -------- |
| Performance     | ≥ 90   | _pending screenshot_ |
| Accessibility   | ≥ 90   | _pending screenshot_ |
| Best Practices  | ≥ 90   | _pending screenshot_ |
| SEO             | ≥ 90   | _pending screenshot_ |

### Optimasi yang sudah diterapkan (M1)

- Font self-hosted (Bunny) dengan `font-display: swap`.
- Code-splitting per Inertia page (`Landing-*.js` terpisah).
- Tailwind 4 tree-shake (build CSS ~34 KB raw, ~7 KB gzip).
- SVG inline favicon (zero HTTP request).
- `prefers-reduced-motion` honored.
- Semantic landmarks: `<header>`, `<nav>`, `<section>` ber-id, `<footer>`.
- Aria-label pada nav, icon button, kontrol slider.
- Kontras teks: `--foreground #1F2937` di atas `--background #F7FAFC`
  (rasio ≈ 12.6 : 1) — AAA.

> Screenshot Lighthouse akan dilampirkan ke folder `docs/screenshots/lighthouse/`
> setelah audit dijalankan oleh anggota tim di lingkungan masing-masing.

---

## 3. Performance — Batch 1.000 Kandidat (M5)

Profiling dijalankan via `tests/Feature/Performance/BatchPerformanceTest.php`
(jalankan dengan `SKIP_PERF_TEST=false vendor/bin/pest tests/Feature/Performance`).

### 3.1 Hasil M2 baseline (SQLite in-memory + queue=sync)

| Metrik           | Target NFR-002 | Hasil M2 | Status |
| ---------------- | -------------- | -------- | ------ |
| Durasi end-to-end | ≤ 5 menit (300 s) | **1,29 s** | ✅ |
| RAM peak         | ≤ 512 MB        | **58 MB**  | ✅ |
| Throughput       | ≥ 200 kandidat/menit | ~46.500 kandidat/menit | ✅ |

### 3.2 Catatan

- Pengukuran dilakukan saat queue=sync sehingga seluruh chunk job berjalan inline
  pada proses test runner. Worker daemon di prod (PostgreSQL + queue=database)
  diharapkan **lebih lambat** karena ada overhead network DB & dispatch
  antar-job, namun tetap jauh di bawah 300 detik.
- Memory peak rendah karena domain layer pure PHP + chunked insert + pelaporan
  hanya rule yang fire (α > 0).
- Re-jalankan test ini dengan PostgreSQL `DB_CONNECTION=pgsql` di M5 sebagai
  validasi NFR-002 final.

---

## 4. Privacy Test (M4)

> NFR-014: Field sensitif (penghasilan, NIM) tidak boleh sampai ke route mahasiswa.

Dijalankan oleh `tests/Feature/Privacy/RankingPrivacyTest.php`. **3 test, 27 assertion, semua hijau.**

| Test                                                              | Status |
| ----------------------------------------------------------------- | ------ |
| Ranking publik hanya kirim `name`, `score`, `status`              | ✅ |
| Dashboard mahasiswa hanya kirim data milik sendiri                | ✅ |
| Mahasiswa di-tolak akses route `/admin/selection/{batch}/audit/.` | ✅ |

## 5. Test Suite Summary (live)| Section                       | File path                                          | Tests | Assertions |
| ----------------------------- | -------------------------------------------------- | ----- | ---------- |
| Auth                          | `tests/Feature/Auth/*`                             | 17    | 49         |
| Domain Fuzzy (manual cases)   | `tests/Feature/Domain/FuzzyEngineManualCasesTest`  | 6     | 23         |
| Admin Criteria & Snapshot     | `tests/Feature/Admin/Criteria*Test`                | 9     | 30         |
| Admin Selection Run           | `tests/Feature/Admin/SelectionRunTest`             | 2     | 9          |
| Mahasiswa Achievement+Profile | `tests/Feature/Mahasiswa/*`                        | 5     | 8          |
| Privacy                       | `tests/Feature/Privacy/*`                          | 3     | 27         |
| Notifications                 | `tests/Feature/NotificationsTest`                  | 4     | 16         |
| Export                        | `tests/Feature/Admin/ExportTest`                   | 5     | 20         |
| Activity Log                  | `tests/Feature/Admin/ActivityLogTest`              | 7     | 20         |
| E2E flows                     | `tests/Feature/E2E/AdminAndMahasiswaFlowTest`      | 2     | 27         |
| Performance (opt-in)          | `tests/Feature/Performance/BatchPerformanceTest`   | 1     | 3          |
| Unit (membership/eligibility/scorer/queue) | `tests/Unit/*`                       | 38    | 49         |
| **Total (incl. perf)**        | —                                                  | **104+** | **275+** |

```bash
vendor/bin/pest --no-coverage           # 104 passed (perf opt-in)
SKIP_PERF_TEST=false vendor/bin/pest --no-coverage   # +1 perf test
```

---

## 6. Aksesibilitas (WCAG 2.1 AA — Self-Check M5)

Landingan dan halaman authenticated dirancang dengan:

- Kontras teks `--foreground (#1F2937)` di atas `--background (#F7FAFC)` rasio ≈ 12.6:1 (lulus AAA).
- Form inputs (`Input.vue`, `Label.vue`) selalu dipasangkan via `for`/`id`.
- `aria-label` di icon-only buttons (bell, theme toggle, logo lockup).
- `prefers-reduced-motion` honored di `Landing.vue`.
- `<header> / <main> / <section> / <footer>` semantik.
- Semua tabel ranking & log memakai `<thead>` + `<tbody>` dengan `<th>` header.

### 6.1 Validasi yang masih manual (di luar test runner)

- Verifikasi screen reader (NVDA / VoiceOver) di halaman approval & ranking.
- Verifikasi focus-order keyboard pada modal Reject (Tab → textarea → tombol Tutup).
- Lighthouse Accessibility audit di Chrome DevTools (target ≥ 90, current placeholder — lampirkan screenshot dari browser anggota tim).

## 7. Demo & Submission Checklist (M5)

- [ ] Tag rilis `m5` setelah seluruh test — incl. perf opt-in — hijau.
- [ ] Lighthouse final audit (Performance ≥ 90, Accessibility ≥ 90) lampirkan ke `docs/screenshots/lighthouse/`.
- [ ] Demo dry-run dengan tim (rekam pain point, fix sebelum sidang).
- [ ] Submission: zip repo + slide presentasi + PDF Test Report.

