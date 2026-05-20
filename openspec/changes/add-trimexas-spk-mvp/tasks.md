## 1. Foundation & Tooling (M1 — Minggu 1)

- [x] 1.1 Inisialisasi project Laravel 13 (`composer create-project laravel/laravel`) dengan PHP 8.3
- [x] 1.2 Tambah Inertia 2 + Vue 3.5 + Vite 6 starter (`composer require inertiajs/inertia-laravel`, `npm install @inertiajs/vue3 vue@^3.5`)
- [x] 1.3 Setup Tailwind 4 dengan plugin Vite (`@tailwindcss/vite`) dan tema Trimexas (CSS variables, dark mode via class `dark`)
- [x] 1.4 Salin komponen dasar shadcn-vue (Button, Input, Label, Card, Dialog, Select, Toast/Sonner) ke `resources/js/components/ui/`
- [x] 1.5 Konfigurasi font Space Grotesk (heading) + Inter (body) dengan `display=swap` di blade layout
- [x] 1.6 Tambah Ziggy (`tightenco/ziggy`) untuk routing client-side
- [x] 1.7 Buat `docker-compose.yml` dengan service `app` (PHP-FPM 8.3), `nginx`, `postgres:16`, `mailpit`; bind volume `.` ke `/var/www`
- [x] 1.8 Buat `Dockerfile` PHP-FPM dengan ekstensi pdo_pgsql, gd, zip, intl, opcache; install Composer & Node 20
- [x] 1.9 Konfigurasi `.env.example` dengan koneksi PostgreSQL dan Mailpit; tambah skrip `composer setup`
- [x] 1.10 Setup Husky + lint-staged + Pint + ESLint + Prettier; pre-commit hook menjalankan format/lint
- [x] 1.11 Buat halaman landing `resources/js/Pages/Landing.vue` dengan section: hero, how it works, features, methodology, about, footer
- [x] 1.12 Daftarkan route `/` ke `LandingController@index` (publik); pastikan tidak butuh autentikasi
- [x] 1.13 Optimasi landing page: lazy-load image, inline critical CSS, defer non-critical scripts, alt text & ARIA
- [x] 1.14 Verifikasi Lighthouse Performance ≥ 90 dan Accessibility ≥ 90 lewat Chrome DevTools, lampirkan screenshot ke `docs/Test_Report.md` (placeholder)
- [x] 1.15 Buat migration utama: `users`, `students`, `student_achievements`, `criteria`, `fuzzy_sets`, `rules`, `output_thresholds`, `selection_batches`, `selection_results`, `selection_rule_evaluations`, `notifications`, plus migration default Laravel `sessions/jobs/failed_jobs/cache`
- [x] 1.16 Install `spatie/laravel-activitylog`, publish & migrate
- [x] 1.17 Tambah seeder `AdminSeeder` (1 admin default), `CriteriaSeeder` (5 kriteria + parameter himpunan default), `OutputThresholdSeeder` (50, 75)
- [x] 1.18 Tambah seeder `RuleSeeder` berisi 75 rule dari `docs/KnowledgeBase_RuleSpec.md` (id R001–R075)
- [x] 1.19 Tag rilis `m1` setelah landing page live + `docker compose up` reproducible

## 2. Auth Multi-role & Account Management 

- [x] 2.1 Tambah enum role di `users.role` (`admin`, `mahasiswa`) dan kolom `approval_status` (`pending`, `approved`, `rejected`), `rejection_reason`, `approved_by`, `approved_at`
- [x] 2.2 Buat `LoginController`, `RegisterController` (mahasiswa only), `LogoutController` dengan Form Request validation
- [x] 2.3 Implement guard logic: pending/rejected mahasiswa ditolak login dengan pesan yang sesuai (lihat spec auth-multirole)
- [x] 2.4 Buat halaman Vue: `Auth/Login.vue`, `Auth/Register.vue` dengan validation inline
- [x] 2.5 Tambah middleware `EnsureAdmin`, `EnsureApprovedStudent`; daftarkan ke route group
- [x] 2.6 Buat halaman admin `Admin/Students/PendingList.vue` dengan tombol Approve/Reject + modal alasan
- [x] 2.7 Implement `ApproveStudentAction`, `RejectStudentAction` (Service); update status, dispatch notifikasi (placeholder, akan diisi di M4)
- [x] 2.8 Tambah session timeout 30 menit (config `session.lifetime=30`); CSRF middleware default Laravel
- [x] 2.9 Tambah Pest test feature: login admin, login mahasiswa pending ditolak, register sukses, logout, route guard 403
- [x] 2.10 Tambah Pest test privacy: route admin tidak bisa diakses mahasiswa

## 3. Domain Layer — Fuzzy Engine 

- [x] 3.1 Buat folder `app/Domain/Fuzzy/` dengan namespace `App\Domain\Fuzzy`; pastikan tidak ada `use Illuminate\*`
- [x] 3.2 Buat DTO `CandidateInput` (struct readonly), `FuzzyResult`, `MembershipMap`, `RuleEvaluation`, `EligibilityResult`
- [x] 3.3 Implement `MembershipFunctions` dengan static method: `linearTurun(x, a, b)`, `segitiga(x, a, b, c)`, `linearNaik(x, a, b)`
- [x] 3.4 Unit test `MembershipFunctionsTest`: cover boundary (x ≤ a, x = b, x ≥ c), in-range, out-of-range; minimal 12 assertion
- [x] 3.5 Implement `Fuzzifier::fuzzify(input, fuzzySetsSnapshot)` mengembalikan map 5 variabel × 3 himpunan
- [x] 3.6 Implement `EligibilityChecker::check(candidate)` dengan 4 gate; mengembalikan `EligibilityResult`
- [x] 3.7 Unit test `EligibilityCheckerTest` dengan kombinasi gate pass/fail
- [x] 3.8 Implement `InferenceEngine::execute(memberships, rulesSnapshot, thresholdsSnapshot)` dengan AND=MIN per rule + z-function untuk 3 consequent
- [x] 3.9 Implement `Defuzzifier::weightedAverage(ruleEvaluations)`; handle case Σα=0 → return 0
- [x] 3.10 Implement `FuzzyEngine::run(candidate, snapshots)` sebagai facade yang memanggil EligibilityChecker → Fuzzifier → InferenceEngine → Defuzzifier
- [x] 3.11 Buat `AchievementScorer` di `app/Domain/Achievement/`: kalkulasi skor entri (level × peringkat) dan agregasi cap 50
- [x] 3.12 Unit test `AchievementScorerTest`: tabel skoring lengkap (4 level × 4 peringkat = 16 cek + agregat cap)
- [x] 3.13 Tulis 5 kasus uji manual `FuzzyEngineTest` dari `docs/KnowledgeBase_RuleSpec.md` §7; selisih ≤ 0,01 vs perhitungan manual
- [x] 3.14 Lengkapi `docs/Test_Report.md` dengan perhitungan manual lengkap untuk 5 kasus uji
<!-- - [x] 3.15 Tag rilis `m2` setelah seluruh test domain green -->

## 4. Criteria Config & Snapshot 

- [x] 4.1 Buat `Admin/CriteriaController` (index, update); Form Request `UpdateFuzzySetRequest` dengan validasi `a < b < c` dan domain range
- [x] 4.2 Buat halaman Vue `Admin/Criteria/Index.vue` daftar kriteria + himpunan + parameter
- [x] 4.3 Komponen `MembershipChart.vue` (canvas/SVG) untuk preview live tiga kurva himpunan; reaktif terhadap perubahan parameter
- [x] 4.4 `Admin/ThresholdController` (update) dengan validasi `threshold_1 < threshold_2` dan range 0-100
- [x] 4.5 Pest test feature: update parameter valid, invalid monotonik ditolak, threshold invalid ditolak
- [x] 4.6 Buat service `SelectionSnapshotService::take(batchId)` yang menyalin state criteria + fuzzy_sets + rules (active=true) + output_thresholds ke kolom JSONB di `selection_batches`
- [x] 4.7 Pest test: edit parameter setelah batch completed tidak mengubah snapshot batch lama

## 5. Selection Batch & Self-Spawning Worker 

- [x] 5.1 Buat `Admin/SelectionController` (run, show, progress) dan halaman Vue `Admin/Selection/Run.vue`, `Admin/Selection/Detail.vue`
- [x] 5.2 Implement `ProcessSelectionBatchJob`: load eligible candidates, chunk 50, dispatch `ProcessCandidateChunkJob` per chunk
- [x] 5.3 Implement `ProcessCandidateChunkJob`: load snapshots, jalankan `FuzzyEngine::run` per kandidat, tulis batch insert ke `selection_results` + `selection_rule_evaluations`, increment `processed_count` batch
- [x] 5.4 Update batch ke `completed` saat `processed_count == total_eligible + total_ineligible`; ke `failed` jika ada chunk yang exhaust retry
- [x] 5.5 Implement `WorkerManager::ensureRunning()` di `app/Domain/Queue/`; cek `storage/app/queue-worker.pid` + `flock`; spawn `php artisan queue:work --queue=seleksi --stop-when-empty` via `Symfony\Process`
- [x] 5.6 Implement signal handler / sweep stale PID (cek `posix_kill($pid, 0)` / Windows fallback `tasklist`)
- [x] 5.7 Pest test `WorkerManagerTest` dengan mock `Symfony\Process`: ensureRunning idempotent, race condition dicegah flock
- [x] 5.8 Buat endpoint progress `GET /admin/selection/{batch}/progress` mengembalikan JSON `{status, total, processed, percentage, error_summary}`
- [x] 5.9 Frontend `Admin/Selection/Detail.vue` polling endpoint progress tiap 2 detik (clearInterval saat status=completed/failed)
- [x] 5.10 Tambah index DB: `students(status, semester, ipk, approval_status)`, `selection_results(batch_id, score)`, `selection_rule_evaluations(batch_id, candidate_id)`
- [x] 5.11 Pest test feature: trigger batch dengan 5 kandidat dummy → status completed, ranking benar

## 6. Achievement & Student Modules 

- [x] 6.1 Buat `Mahasiswa/AchievementController` (index, store, update, destroy) dengan validation 5 entri max, kombinasi level×peringkat valid
- [x] 6.2 Buat halaman `Mahasiswa/Profile.vue` + `Mahasiswa/Achievements.vue` (form + list) dengan toast feedback
- [x] 6.3 Tambah accessor `Student::agregat_akademis`, `Student::agregat_non_akademis` (cap 50)
- [x] 6.4 Pest test: tambah entri ke-6 ditolak, edit entri verified ditolak, agregat cap 50
- [x] 6.5 Buat `Admin/StudentController` (CRUD): index dengan paginasi 25 + search nama/NIM, store/update/destroy dengan Form Request validation domain (IPK 3,00-4,00 dst)
- [x] 6.6 Implement guard "edit profil terkunci saat batch running" di `UpdateOwnProfileRequest`
- [x] 6.7 Buat `Admin/AchievementVerificationController` (verify, unverify) dan UI checkbox di halaman detail mahasiswa
- [x] 6.8 Pest test: edit profil saat batch running ditolak HTTP 409

## 7. Selection Results UI & Privacy

- [x] 7.1 Buat halaman `Admin/Selection/Detail.vue` dengan tabel ranking sortable, section "Tidak Memenuhi Syarat" terpisah
- [x] 7.2 Buat halaman `Admin/Selection/Audit.vue` per kandidat: input crisp, fuzzifikasi 5×3, daftar rule aktif (α, z), defuzzifikasi
- [x] 7.3 Buat halaman `Mahasiswa/Ranking.vue` dengan kolom Nama, Skor, Status saja; controller hanya pass 3 field via Inertia
- [x] 7.4 Buat halaman `Admin/History.vue` daftar batch + filter status
- [x] 7.5 Buat halaman `Mahasiswa/Dashboard.vue` menampilkan status pengajuan + skor sendiri (jika ada)
- [x] 7.6 Pest test `RankingPrivacyTest`: assert Inertia props `/mahasiswa/ranking` tidak punya kolom `nim`, `penghasilan`, `audit_*`, `ineligibility_reasons`
- [x] 7.7 Pest test `RankingPrivacyTest::test_dashboard_only_returns_own_data`

## 8. Notifications 

- [x] 8.1 Buat tabel `notifications` (jika belum ada di M1) sesuai Laravel default + tambah kolom `type`, `read_at`
- [x] 8.2 Buat Notifikasi Laravel: `AccountApprovedNotification`, `AccountRejectedNotification`, `BatchCompletedNotification`, `BatchFailedNotification` dengan channel `database` + `mail`
- [x] 8.3 Konfigurasi queue terpisah `notifications` di `.env`; trigger `Notification::send()->locale('id')`
- [x] 8.4 Buat Mailable: `AccountApprovedMail`, `AccountRejectedMail`; template Blade Markdown sederhana di `resources/views/emails/`
- [x] 8.5 Trigger notifikasi di `ApproveStudentAction`, `RejectStudentAction`, `ProcessSelectionBatchJob` (event listener saat completed)
- [x] 8.6 Buat komponen Vue `Notifications/BellDropdown.vue` di header authenticated layout dengan polling 30 detik (tidak realtime, MVP)
- [x] 8.7 Endpoint `GET /notifications` (paginated) + `POST /notifications/mark-read`
- [x] 8.8 Pest test: approve mahasiswa → notifikasi DB tercipta + Mail di-fake terkirim; bell counter benar

## 9. Report Export 

- [x] 9.1 Install `league/csv ^9` dan `barryvdh/laravel-dompdf ^3` via composer
- [x] 9.2 Buat `Admin/ExportController@csv` mengembalikan StreamedResponse via League CSV writer
- [x] 9.3 Buat `Admin/ExportController@pdf` (ranking) memakai DomPDF dengan template `resources/views/exports/ranking.blade.php`
- [x] 9.4 Buat `Admin/ExportController@auditPdf` (per kandidat) dengan template `resources/views/exports/audit.blade.php`
- [x] 9.5 Tambah footer reproducibility (snapshot ID + timestamp) ke template PDF
- [x] 9.6 Authorization: hanya admin (test export oleh mahasiswa → 403, anonymous → redirect login)
- [x] 9.7 Pest test feature: export CSV & PDF mengembalikan attachment dengan filename benar; 1.000 row tidak menyebabkan memory > 128MB

## 10. Activity Log 

- [x] 10.1 Tambah trait `LogsActivity` ke model `User`, `Student`, `StudentAchievement`, `FuzzySet`, `OutputThreshold`, `SelectionBatch` dengan kolom yang relevan dilacak
- [x] 10.2 Tambah `activity()->log()` manual di `ApproveStudentAction`, `RejectStudentAction`, `ProcessSelectionBatchJob`, `ExportController`
- [x] 10.3 Buat `Admin/ActivityLogController` (index dengan filter user, log_name, tanggal); halaman Vue `Admin/ActivityLog/Index.vue` paginasi 50
- [x] 10.4 Pastikan tidak ada route `DELETE /admin/activity-log/{id}` (tidak terdaftar)
- [x] 10.5 Pest test: aksi penting tercatat (login, approve, edit param, run selection, export); halaman log menampilkan benar dengan filter

## 11. Integration & Validation 

- [x] 11.1 End-to-end test alur admin lengkap: Login → Approve mahasiswa → Edit kriteria → Run Selection → Lihat ranking → Audit trail → Export CSV/PDF
- [x] 11.2 End-to-end test alur mahasiswa: Register → Tunggu approval → Login → Lihat status → Lihat ranking publik
- [x] 11.3 Profiling batch 1.000 kandidat dummy: ukur durasi, RAM peak, query count; catat di `docs/Test_Report.md`
- [x] 11.4 Verifikasi NFR-002 (≤ 5 menit untuk 1.000 kandidat); jika gagal, terapkan mitigasi R-D11 (index, lazy collections, batch insert audit)
- [x] 11.5 Validasi Acceptance Criteria #2: rerun 5 kasus uji manual via Pest, attach output ke Test Report
- [x] 11.6 Lengkapi `docs/Setup_Guide.md` dengan langkah Docker + worker manual fallback Windows
- [x] 11.7 Lengkapi `docs/User_Manual.md` dengan screenshot alur admin & mahasiswa
- [x] 11.8 Cek WCAG sederhana: kontras AA, alt text, focus order pada landing + halaman authenticated
- [ ] 11.9 Lighthouse audit final landing page; pastikan ≥ 90 / ≥ 90; lampirkan ke Test Report
- [ ] 11.10 Demo dry-run dengan tim; rekam pain point dan fix
- [x] 11.11 Tag rilis `m5` (final); siapkan submission package: zip repo, slide presentasi, PDF Test Report
