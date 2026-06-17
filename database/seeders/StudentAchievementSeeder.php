<?php

namespace Database\Seeders;

use App\Domain\Achievement\AchievementScorer;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data prestasi mahasiswa untuk pengujian Fuzzy MAMDANI.
 *
 * Strategi scoring (AchievementScorer):
 *   internasional: juara_1=10, juara_2=8, juara_3=6, partisipasi=4
 *   nasional:      juara_1=7,  juara_2=5, juara_3=3, partisipasi=2
 *   provinsi:      juara_1=4,  juara_2=3, juara_3=2, partisipasi=1
 *   kabupaten:     juara_1=2,  juara_2=1.5, juara_3=1, partisipasi=0.5
 *
 * Cap agregat per kategori: 50 poin.
 *
 * Fuzzy set boundaries (prestasi):
 *   sedikit: 0-15 (linear turun dari 5 ke 15)
 *   sedang:  5-25 (segitiga, peak di 15)
 *   banyak:  15-50 (linear naik dari 15 ke 25)
 *
 * Desain data:
 *   #1-5   (Layak)         → prestasi akademis & non-akademis banyak (agregat >25)
 *   #6-10  (Dipertimbangkan) → prestasi sedang (agregat 10-20)
 *   #11-15 (Tidak Layak)   → prestasi sedikit/tidak ada (agregat <10)
 */
class StudentAchievementSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::where('role', User::ROLE_ADMIN)->first();
        $students = Student::orderBy('id')->get()->values();

        if ($students->count() < 15) {
            $this->command->warn('StudentSeeder harus dijalankan terlebih dahulu.');

            return;
        }

        $allAchievements = $this->buildAchievements();

        foreach ($allAchievements as $studentIdx => $achievements) {
            $student = $students[$studentIdx];

            foreach ($achievements as $a) {
                $score = AchievementScorer::scoreFor($a['level'], $a['rank']);

                StudentAchievement::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'title'      => $a['title'],
                        'year'       => $a['year'],
                    ],
                    [
                        'category'          => $a['category'],
                        'level'             => $a['level'],
                        'rank'              => $a['rank'],
                        'score'             => $score,
                        'verified_by_admin' => true,
                        'verified_by'       => $admin?->id,
                        'verified_at'       => now(),
                    ],
                );
            }
        }
    }

    /**
     * @return array<int, list<array{title:string, category:string, level:string, rank:string, year:int}>>
     */
    private function buildAchievements(): array
    {
        return [
            // ══════════════════════════════════════════════════════
            // LAYAK — Prestasi banyak (agregat akademis & non-akademis >25)
            // ══════════════════════════════════════════════════════

            // #1 Ahmad Fauzi — akademis: 7+5+4=16 + non-akademis: 10+8+7=25 → ak=16, non=25
            0 => [
                ['title' => 'Juara 2 Olimpiade Matematika Nasional',            'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_2',     'year' => 2025],
                ['title' => 'Juara 1 Debat Bahasa Inggris Provinsi Jawa Barat', 'category' => 'akademis',     'level' => 'provinsi',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 1 Lomba Pidato Internasional',              'category' => 'non_akademis',  'level' => 'internasional', 'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 2 Festival Seni Budaya Nasional',           'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'juara_2',     'year' => 2024],
                ['title' => 'Juara 1 Lomba Cerdas Cermat Nasional',            'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_1',     'year' => 2024],
            ],

            // #2 Siti Nurhaliza — akademis: 10+7=17 + non-akademis: 10+8=18 → ak=17, non=18
            1 => [
                ['title' => 'Juara 1 Kompetisi Riset Ilmiah Internasional',     'category' => 'akademis',     'level' => 'internasional', 'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 1 Hackathon Nasional',                       'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_1',     'year' => 2024],
                ['title' => 'Juara 1 Lomba Tari Tradisional Internasional',     'category' => 'non_akademis',  'level' => 'internasional', 'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 2 Lomba Fotografi Nasional',                'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'juara_2',     'year' => 2024],
            ],

            // #3 Budi Santoso — akademis: 8+6=14 + non-akademis: 7+7=14 → ak=14, non=14
            2 => [
                ['title' => 'Juara 2 Olimpiade Fisika Internasional',           'category' => 'akademis',     'level' => 'internasional', 'rank' => 'juara_2',     'year' => 2025],
                ['title' => 'Juara 3 Olimpiade Kimia Internasional',           'category' => 'akademis',     'level' => 'internasional', 'rank' => 'juara_3',     'year' => 2024],
                ['title' => 'Juara 1 Lomba Paduan Suara Nasional',             'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 1 Lomba Desain Poster Nasional',            'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'juara_1',     'year' => 2024],
            ],

            // #4 Dewi Kartika — akademis: 7+5+4=16 + non-akademis: 10+6=16 → ak=16, non=16
            3 => [
                ['title' => 'Juara 1 Lomba Esai Ilmiah Nasional',              'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 2 Kompetisi Penulisan Karya Ilmiah Nasional','category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_2',     'year' => 2025],
                ['title' => 'Juara 1 Lomba Debat Provinsi D.I. Yogyakarta',    'category' => 'akademis',     'level' => 'provinsi',      'rank' => 'juara_1',     'year' => 2024],
                ['title' => 'Juara 1 Lomba Musik Tradisional Internasional',   'category' => 'non_akademis',  'level' => 'internasional', 'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 3 Lomba Teater Internasional',              'category' => 'non_akademis',  'level' => 'internasional', 'rank' => 'juara_3',     'year' => 2024],
            ],

            // #5 Rizky Ramadhan — akademis: 7+4=11 + non-akademis: 8+7=15 → ak=11, non=15
            4 => [
                ['title' => 'Juara 1 Kompetisi Programming Nasional',          'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 1 Lomba Inovasi Teknologi Provinsi',        'category' => 'akademis',     'level' => 'provinsi',      'rank' => 'juara_1',     'year' => 2024],
                ['title' => 'Juara 2 Lomba Pencak Silat Internasional',        'category' => 'non_akademis',  'level' => 'internasional', 'rank' => 'juara_2',     'year' => 2025],
                ['title' => 'Juara 1 Lomba Bela Diri Nasional',                'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'juara_1',     'year' => 2024],
            ],

            // ══════════════════════════════════════════════════════
            // DIPERTIMBANGKAN — Prestasi sedang (agregat ~10-20)
            // ══════════════════════════════════════════════════════

            // #6 Anisa Widya — akademis: 5+3=8 + non-akademis: 4+3=7 → ak=8, non=7
            5 => [
                ['title' => 'Juara 2 Lomba Penelitian Mahasiswa Nasional',     'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_2',     'year' => 2025],
                ['title' => 'Juara 3 Olimpiade Biologi Nasional',             'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_3',     'year' => 2024],
                ['title' => 'Partisipasi Konferensi Pemuda Internasional',     'category' => 'non_akademis',  'level' => 'internasional', 'rank' => 'partisipasi', 'year' => 2025],
                ['title' => 'Juara 3 Lomba Memasak Provinsi',                 'category' => 'non_akademis',  'level' => 'provinsi',      'rank' => 'juara_3',     'year' => 2024],
            ],

            // #7 Eko Prasetyo — akademis: 4+3=7 + non-akademis: 5+2=7 → ak=7, non=7
            6 => [
                ['title' => 'Juara 1 Lomba Studi Kasus Provinsi Jawa Tengah', 'category' => 'akademis',     'level' => 'provinsi',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 2 Lomba Karya Tulis Provinsi',             'category' => 'akademis',     'level' => 'provinsi',      'rank' => 'juara_2',     'year' => 2024],
                ['title' => 'Juara 2 Lomba Band Nasional',                    'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'juara_2',     'year' => 2025],
                ['title' => 'Partisipasi Festival Seni Nasional',             'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'partisipasi', 'year' => 2024],
            ],

            // #8 Fitri Handayani — akademis: 7 + non-akademis: 4 → ak=7, non=4
            7 => [
                ['title' => 'Juara 1 Lomba Statistik Nasional',               'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Partisipasi Konferensi Internasional Kesehatan', 'category' => 'non_akademis',  'level' => 'internasional', 'rank' => 'partisipasi', 'year' => 2024],
            ],

            // #9 Galih Permana — akademis: 5+4+4=13 + non-akademis: 3+2=5 → ak=13, non=5
            8 => [
                ['title' => 'Juara 2 Kompetisi Bisnis Plan Nasional',         'category' => 'akademis',     'level' => 'nasional',      'rank' => 'juara_2',     'year' => 2025],
                ['title' => 'Juara 1 Lomba Presentasi Ilmiah Provinsi',       'category' => 'akademis',     'level' => 'provinsi',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 1 Lomba Poster Ilmiah Provinsi',           'category' => 'akademis',     'level' => 'provinsi',      'rank' => 'juara_1',     'year' => 2024],
                ['title' => 'Juara 3 Lomba Voli Nasional',                    'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'juara_3',     'year' => 2025],
                ['title' => 'Partisipasi Turnamen E-Sport Nasional',          'category' => 'non_akademis',  'level' => 'nasional',      'rank' => 'partisipasi', 'year' => 2024],
            ],

            // #10 Hani Oktaviani — akademis: 4+2=6 + non-akademis: 4+3=7 → ak=6, non=7
            9 => [
                ['title' => 'Juara 1 Lomba Pembaca Puisi Provinsi',           'category' => 'akademis',     'level' => 'provinsi',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Partisipasi Lomba Cerpen Nasional',              'category' => 'akademis',     'level' => 'nasional',      'rank' => 'partisipasi', 'year' => 2024],
                ['title' => 'Juara 1 Lomba Lukis Provinsi Banten',            'category' => 'non_akademis',  'level' => 'provinsi',      'rank' => 'juara_1',     'year' => 2025],
                ['title' => 'Juara 3 Lomba Dance Cover Provinsi',             'category' => 'non_akademis',  'level' => 'provinsi',      'rank' => 'juara_3',     'year' => 2024],
            ],

            // ══════════════════════════════════════════════════════
            // TIDAK LAYAK — Prestasi sedikit/tidak ada (agregat <10)
            // ══════════════════════════════════════════════════════

            // #11 Irfan Maulana — akademis: 2 + non-akademis: 1 → ak=2, non=1
            10 => [
                ['title' => 'Partisipasi Seminar Akademik Nasional',          'category' => 'akademis',     'level' => 'nasional',      'rank' => 'partisipasi', 'year' => 2025],
                ['title' => 'Partisipasi Lomba Cerdas Cermat Provinsi',       'category' => 'non_akademis',  'level' => 'provinsi',      'rank' => 'partisipasi', 'year' => 2024],
            ],

            // #12 Joko Susilo — akademis: 1.5 + non-akademis: 0.5 → ak=1.5, non=0.5
            11 => [
                ['title' => 'Juara 2 Lomba Cerdas Cermat Kabupaten',          'category' => 'akademis',     'level' => 'kabupaten',     'rank' => 'juara_2',     'year' => 2025],
                ['title' => 'Partisipasi Lomba Futsal Kabupaten',             'category' => 'non_akademis',  'level' => 'kabupaten',     'rank' => 'partisipasi', 'year' => 2024],
            ],

            // #13 Kartini Wulandari — akademis: 2 + non-akademis: 0 → ak=2, non=0
            12 => [
                ['title' => 'Partisipasi Lomba Menulis Essay Kabupaten',      'category' => 'akademis',     'level' => 'kabupaten',     'rank' => 'partisipasi', 'year' => 2025],
                ['title' => 'Partisipasi Lomba Baca Puisi Kabupaten',          'category' => 'akademis',     'level' => 'kabupaten',     'rank' => 'partisipasi', 'year' => 2024],
            ],

            // #14 Lukman Hakim — akademis: 1 + non-akademis: 1 → ak=1, non=1
            13 => [
                ['title' => 'Juara 3 Lomba Matematika Kabupaten',             'category' => 'akademis',     'level' => 'kabupaten',     'rank' => 'juara_3',     'year' => 2025],
                ['title' => 'Juara 3 Lomba Badminton Kabupaten',              'category' => 'non_akademis',  'level' => 'kabupaten',     'rank' => 'juara_3',     'year' => 2024],
            ],

            // #15 Maya Anggraini — no achievements → ak=0, non=0
            14 => [],
        ];
    }
}
