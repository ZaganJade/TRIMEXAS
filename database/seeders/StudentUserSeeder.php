<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Membuat akun user mahasiswa yang sudah di-approve.
 * Digunakan bareng StudentSeeder & StudentAchievementSeeder.
 */
class StudentUserSeeder extends Seeder
{
    /**
     * @return array<int, User>  keyed by array index (same order as profiles in StudentSeeder)
     */
    public function run(): array
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $students = [
            // ── Layak (kandidat ideal) ──────────────────────────
            ['name' => 'Ahmad Fauzi Rahman',      'email' => 'ahmad.fauzi@student.ac.id'],
            ['name' => 'Siti Nurhaliza',          'email' => 'siti.nurhaliza@student.ac.id'],
            ['name' => 'Budi Santoso',            'email' => 'budi.santoso@student.ac.id'],
            ['name' => 'Dewi Kartika Sari',       'email' => 'dewi.kartika@student.ac.id'],
            ['name' => 'Rizky Ramadhan',          'email' => 'rizky.ramadhan@student.ac.id'],

            // ── Dipertimbangkan (borderline) ────────────────────
            ['name' => 'Anisa Widya Putri',       'email' => 'anisa.widya@student.ac.id'],
            ['name' => 'Eko Prasetyo',            'email' => 'eko.prasetyo@student.ac.id'],
            ['name' => 'Fitri Handayani',         'email' => 'fitri.handayani@student.ac.id'],
            ['name' => 'Galih Permana',           'email' => 'galih.permana@student.ac.id'],
            ['name' => 'Hani Oktaviani',          'email' => 'hani.oktaviani@student.ac.id'],

            // ── Tidak Layak (profil lemah) ─────────────────────
            ['name' => 'Irfan Maulana',           'email' => 'irfan.maulana@student.ac.id'],
            ['name' => 'Joko Susilo',             'email' => 'joko.susilo@student.ac.id'],
            ['name' => 'Kartini Wulandari',       'email' => 'kartini.wulan@student.ac.id'],
            ['name' => 'Lukman Hakim',            'email' => 'lukman.hakim@student.ac.id'],
            ['name' => 'Maya Anggraini',          'email' => 'maya.anggraini@student.ac.id'],
        ];

        $created = [];

        foreach ($students as $data) {
            $created[] = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => 'password',   // auto-hashed via cast
                    'role'              => User::ROLE_MAHASISWA,
                    'approval_status'   => User::STATUS_APPROVED,
                    'approved_by'       => $admin?->id,
                    'approved_at'       => now(),
                    'email_verified_at' => now(),
                ],
            );
        }

        return $created;
    }
}
