<?php

namespace Database\Seeders;

use App\Models\OutputThreshold;
use Illuminate\Database\Seeder;

class OutputThresholdSeeder extends Seeder
{
    public function run(): void
    {
        // Mark existing actives as not active so we have one canonical row.
        OutputThreshold::query()->update(['is_active' => false]);

        OutputThreshold::firstOrCreate(
            ['threshold_1' => 50, 'threshold_2' => 75],
            ['is_active' => true]
        )->update(['is_active' => true]);
    }
}
