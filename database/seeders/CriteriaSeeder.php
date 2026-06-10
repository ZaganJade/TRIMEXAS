<?php

namespace Database\Seeders;

use App\Models\Criterion;
use App\Models\FuzzySet;
use Illuminate\Database\Seeder;

/**
 * Seeder kriteria + himpunan fuzzy default sesuai PRD §8.4.
 *
 * IPK (3,00 - 4,00):
 *   rendah  : linear_turun a=3.00 b=3.25
 *   sedang  : segitiga    a=3.00 b=3.50 c=3.75
 *   tinggi  : linear_naik a=3.50 b=3.75
 *
 *   Referensi: Gloria & Sediyono (2022), Muhtadi et al. (2025)
 *   Perubahan: rendah dipersempit ke 3.0-3.25 (borderline saja),
 *   sedang diperluas ke 3.0-3.75, tinggi dimulai dari 3.5.
 *
 * Penghasilan Ortu (0 - 15.000.000 rupiah):
 *   rendah  : linear_turun a=3_000_000 b=7_000_000
 *   sedang  : segitiga    a=3_000_000 b=5_000_000 c=10_000_000
 *   tinggi  : linear_naik a=7_000_000 b=10_000_000
 *
 * Prestasi Akademis (0 - 50 poin):
 *   sedikit : linear_turun a=5  b=15
 *   sedang  : segitiga     a=5  b=15 c=25
 *   banyak  : linear_naik  a=15 b=25
 *
 * Prestasi Non-Akademis (0 - 50 poin): identik dengan akademis.
 *
 * Tanggungan (0 - 8 orang):
 *   sedikit : linear_turun a=2 b=4
 *   sedang  : segitiga     a=2 b=3 c=4
 *   banyak  : linear_naik  a=2 b=4
 */
class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteriaData = [
            [
                'code' => 'ipk',
                'name' => 'IPK',
                'domain_min' => 3.00, 'domain_max' => 4.00,
                'unit' => 'skala 4',
                'display_order' => 1,
                'sets' => [
                    ['name' => 'rendah', 'shape' => FuzzySet::SHAPE_LINEAR_TURUN, 'a' => 3.00, 'b' => 3.25, 'c' => null],
                    ['name' => 'sedang', 'shape' => FuzzySet::SHAPE_SEGITIGA, 'a' => 3.00, 'b' => 3.50, 'c' => 3.75],
                    ['name' => 'tinggi', 'shape' => FuzzySet::SHAPE_LINEAR_NAIK, 'a' => 3.50, 'b' => 3.75, 'c' => null],
                ],
            ],
            [
                'code' => 'penghasilan',
                'name' => 'Penghasilan Orang Tua',
                'domain_min' => 0, 'domain_max' => 15_000_000,
                'unit' => 'rupiah/bulan',
                'display_order' => 2,
                'sets' => [
                    ['name' => 'rendah', 'shape' => FuzzySet::SHAPE_LINEAR_TURUN, 'a' => 3_000_000, 'b' => 7_000_000, 'c' => null],
                    ['name' => 'sedang', 'shape' => FuzzySet::SHAPE_SEGITIGA, 'a' => 3_000_000, 'b' => 5_000_000, 'c' => 10_000_000],
                    ['name' => 'tinggi', 'shape' => FuzzySet::SHAPE_LINEAR_NAIK, 'a' => 7_000_000, 'b' => 10_000_000, 'c' => null],
                ],
            ],
            [
                'code' => 'prestasi_akademis',
                'name' => 'Prestasi Akademis',
                'domain_min' => 0, 'domain_max' => 50,
                'unit' => 'poin',
                'display_order' => 3,
                'sets' => [
                    ['name' => 'sedikit', 'shape' => FuzzySet::SHAPE_LINEAR_TURUN, 'a' => 5, 'b' => 15, 'c' => null],
                    ['name' => 'sedang', 'shape' => FuzzySet::SHAPE_SEGITIGA, 'a' => 5, 'b' => 15, 'c' => 25],
                    ['name' => 'banyak', 'shape' => FuzzySet::SHAPE_LINEAR_NAIK, 'a' => 15, 'b' => 25, 'c' => null],
                ],
            ],
            [
                'code' => 'prestasi_non_akademis',
                'name' => 'Prestasi Non-Akademis',
                'domain_min' => 0, 'domain_max' => 50,
                'unit' => 'poin',
                'display_order' => 4,
                'sets' => [
                    ['name' => 'sedikit', 'shape' => FuzzySet::SHAPE_LINEAR_TURUN, 'a' => 5, 'b' => 15, 'c' => null],
                    ['name' => 'sedang', 'shape' => FuzzySet::SHAPE_SEGITIGA, 'a' => 5, 'b' => 15, 'c' => 25],
                    ['name' => 'banyak', 'shape' => FuzzySet::SHAPE_LINEAR_NAIK, 'a' => 15, 'b' => 25, 'c' => null],
                ],
            ],
            [
                'code' => 'tanggungan',
                'name' => 'Jumlah Tanggungan Keluarga',
                'domain_min' => 0, 'domain_max' => 8,
                'unit' => 'orang',
                'display_order' => 5,
                'sets' => [
                    ['name' => 'sedikit', 'shape' => FuzzySet::SHAPE_LINEAR_TURUN, 'a' => 2, 'b' => 4, 'c' => null],
                    ['name' => 'sedang', 'shape' => FuzzySet::SHAPE_SEGITIGA, 'a' => 2, 'b' => 3, 'c' => 4],
                    ['name' => 'banyak', 'shape' => FuzzySet::SHAPE_LINEAR_NAIK, 'a' => 2, 'b' => 4, 'c' => null],
                ],
            ],
        ];

        foreach ($criteriaData as $data) {
            $sets = $data['sets'];
            unset($data['sets']);

            $criterion = Criterion::updateOrCreate(['code' => $data['code']], $data);

            foreach ($sets as $set) {
                FuzzySet::updateOrCreate(
                    ['criterion_id' => $criterion->id, 'name' => $set['name']],
                    $set
                );
            }
        }
    }
}
