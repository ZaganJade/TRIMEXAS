<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data profil mahasiswa untuk pengujian Fuzzy MAMDANI.
 *
 * 15 mahasiswa dengan profil bervariasi:
 *   #1-5   → kandidat ideal         (IPK tinggi, penghasilan rendah, tanggungan banyak)
 *   #6-10  → borderline             (profil campuran, perlu kompensasi)
 *   #11-15 → profil lemah           (IPK rendah, penghasilan tinggi, tanggungan sedikit)
 *
 * Variasi dirancang agar men-trigger rule LAYAK / DIPERTIMBANGKAN / TIDAK_LAYAK
 * sesuai rule base yang ada di RuleSeeder.
 */
class StudentSeeder extends Seeder
{
    /**
     * @return array<int, Student>
     */
    public function run(): array
    {
        $users = User::where('role', User::ROLE_MAHASISWA)
            ->where('approval_status', User::STATUS_APPROVED)
            ->orderBy('id')
            ->get()
            ->values();

        if ($users->count() < 15) {
            $this->command->warn('StudentUserSeeder harus dijalankan terlebih dahulu.');

            return [];
        }

        $profiles = [
            // ── Layak (kandidat ideal) ──────────────────────────
            // #1 — Kandidat sempurna: IPK tinggi, miskin, tanggungan besar
            ['nim' => '2024001001', 'full_name' => 'Ahmad Fauzi Rahman',    'semester' => 4,  'ipk' => 3.85, 'penghasilan_ortu' => 2000000,  'tanggungan' => 6, 'phone' => '081234567801', 'address' => 'Jl. Melati No. 10, Bandung'],
            // #2 — Sangat layak: IPK tinggi, penghasilan rendah
            ['nim' => '2024001002', 'full_name' => 'Siti Nurhaliza',        'semester' => 6,  'ipk' => 3.92, 'penghasilan_ortu' => 1500000,  'tanggungan' => 5, 'phone' => '081234567802', 'address' => 'Jl. Anggrek No. 5, Jakarta'],
            // #3 — Layak: IPK tinggi, ekonomi sulit
            ['nim' => '2024001003', 'full_name' => 'Budi Santoso',          'semester' => 4,  'ipk' => 3.78, 'penghasilan_ortu' => 2500000,  'tanggungan' => 7, 'phone' => '081234567803', 'address' => 'Jl. Kenanga No. 3, Surabaya'],
            // #4 — Layak: IPK cukup tinggi, miskin, tanggungan besar
            ['nim' => '2024001004', 'full_name' => 'Dewi Kartika Sari',     'semester' => 5,  'ipk' => 3.60, 'penghasilan_ortu' => 1800000,  'tanggungan' => 6, 'phone' => '081234567804', 'address' => 'Jl. Dahlia No. 8, Yogyakarta'],
            // #5 — Layak: IPK tinggi, ekonomi rendah, tanggungan sedang
            ['nim' => '2024001005', 'full_name' => 'Rizky Ramadhan',        'semester' => 3,  'ipk' => 3.75, 'penghasilan_ortu' => 3000000,  'tanggungan' => 4, 'phone' => '081234567805', 'address' => 'Jl. Mawar No. 12, Semarang'],

            // ── Dipertimbangkan (borderline) ────────────────────
            // #6 — IPK sedang, ekonomi rendah, tanggungan besar
            ['nim' => '2024001006', 'full_name' => 'Anisa Widya Putri',     'semester' => 4,  'ipk' => 3.45, 'penghasilan_ortu' => 2500000,  'tanggungan' => 5, 'phone' => '081234567806', 'address' => 'Jl. Flamboyan No. 7, Malang'],
            // #7 — IPK sedang, ekonomi menengah, tanggungan sedang
            ['nim' => '2024001007', 'full_name' => 'Eko Prasetyo',          'semester' => 5,  'ipk' => 3.50, 'penghasilan_ortu' => 5500000,  'tanggungan' => 3, 'phone' => '081234567807', 'address' => 'Jl. Cempaka No. 15, Depok'],
            // #8 — IPK sedang, ekonomi rendah, tanggungan sedikit
            ['nim' => '2024001008', 'full_name' => 'Fitri Handayani',       'semester' => 6,  'ipk' => 3.40, 'penghasilan_ortu' => 3000000,  'tanggungan' => 2, 'phone' => '081234567808', 'address' => 'Jl. Teratai No. 20, Bogor'],
            // #9 — IPK borderline, prestasi kuat, ekonomi sedang
            ['nim' => '2024001009', 'full_name' => 'Galih Permana',         'semester' => 3,  'ipk' => 3.30, 'penghasilan_ortu' => 6000000,  'tanggungan' => 4, 'phone' => '081234567809', 'address' => 'Jl. Bougenville No. 9, Tangerang'],
            // #10 — IPK sedang-atas, ekonomi menengah, tanggungan sedikit
            ['nim' => '2024001010', 'full_name' => 'Hani Oktaviani',        'semester' => 4,  'ipk' => 3.55, 'penghasilan_ortu' => 8000000,  'tanggungan' => 2, 'phone' => '081234567810', 'address' => 'Jl. Sakura No. 11, Bekasi'],

            // ── Tidak Layak (profil lemah) ─────────────────────
            // #11 — IPK rendah, ekonomi menengah, tanggungan sedikit
            ['nim' => '2024001011', 'full_name' => 'Irfan Maulana',         'semester' => 4,  'ipk' => 3.05, 'penghasilan_ortu' => 9000000,  'tanggungan' => 1, 'phone' => '081234567811', 'address' => 'Jl. Mangga No. 4, Medan'],
            // #12 — IPK rendah, ekonomi tinggi, tanpa tanggungan
            ['nim' => '2024001012', 'full_name' => 'Joko Susilo',           'semester' => 5,  'ipk' => 3.10, 'penghasilan_ortu' => 12000000, 'tanggungan' => 1, 'phone' => '081234567812', 'address' => 'Jl. Jambu No. 6, Makassar'],
            // #13 — IPK sangat rendah, ekonomi menengah, tanggungan sedikit
            ['nim' => '2024001013', 'full_name' => 'Kartini Wulandari',     'semester' => 3,  'ipk' => 3.02, 'penghasilan_ortu' => 7000000,  'tanggungan' => 2, 'phone' => '081234567813', 'address' => 'Jl. Rambutan No. 14, Palembang'],
            // #14 — IPK rendah, ekonomi cukup baik, tanggungan sedikit
            ['nim' => '2024001014', 'full_name' => 'Lukman Hakim',          'semester' => 6,  'ipk' => 3.15, 'penghasilan_ortu' => 10000000, 'tanggungan' => 2, 'phone' => '081234567814', 'address' => 'Jl. Salak No. 18, Denpasar'],
            // #15 — IPK rendah, ekonomi tinggi, tanggungan sedikit
            ['nim' => '2024001015', 'full_name' => 'Maya Anggraini',        'semester' => 4,  'ipk' => 3.08, 'penghasilan_ortu' => 14000000, 'tanggungan' => 1, 'phone' => '081234567815', 'address' => 'Jl. Nanas No. 22, Manado'],
        ];

        $created = [];

        foreach ($profiles as $i => $data) {
            $data['user_id'] = $users[$i]->id;
            $data['status']  = 'aktif';

            $created[] = Student::updateOrCreate(
                ['nim' => $data['nim']],
                $data,
            );
        }

        return $created;
    }
}
