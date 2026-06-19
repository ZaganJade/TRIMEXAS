<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@trimexas.local'],
            [
                'name' => 'Administrator Trimexas',
                'password' => 'trimexas-admin', // hashed via cast
                'role' => User::ROLE_ADMIN,
                'approval_status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );
    }
}