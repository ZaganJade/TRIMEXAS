<?php

declare(strict_types=1);

namespace App\Domain\Achievement;

/**
 * Skoring entri prestasi:
 *   level × peringkat -> skor numerik (PRD §8.3)
 *
 * Agregasi:
 *   Σ(skor entri) per kategori, di-cap 50.
 *
 * Domain murni — tidak menyentuh Eloquent / Illuminate.
 */
final class AchievementScorer
{
    public const CATEGORY_AKADEMIS = 'akademis';
    public const CATEGORY_NON_AKADEMIS = 'non_akademis';

    public const AGGREGATE_CAP = 50.0;

    /**
     * Tabel skor (level x peringkat) sesuai PRD §8.3.
     *
     * @var array<string, array<string, float>>
     */
    private const SCORE_TABLE = [
        'internasional' => ['juara_1' => 10.0, 'juara_2' => 8.0, 'juara_3' => 6.0, 'partisipasi' => 4.0],
        'nasional' => ['juara_1' => 7.0,  'juara_2' => 5.0, 'juara_3' => 3.0, 'partisipasi' => 2.0],
        'provinsi' => ['juara_1' => 4.0,  'juara_2' => 3.0, 'juara_3' => 2.0, 'partisipasi' => 1.0],
        'kabupaten' => ['juara_1' => 2.0,  'juara_2' => 1.5, 'juara_3' => 1.0, 'partisipasi' => 0.5],
    ];

    public static function scoreFor(string $level, string $rank): float
    {
        if (! isset(self::SCORE_TABLE[$level][$rank])) {
            throw new \InvalidArgumentException(
                "Kombinasi level/peringkat tidak valid: {$level} / {$rank}"
            );
        }

        return self::SCORE_TABLE[$level][$rank];
    }

    /**
     * Daftar level yang valid.
     *
     * @return list<string>
     */
    public static function levels(): array
    {
        return array_keys(self::SCORE_TABLE);
    }

    /**
     * Daftar peringkat yang valid.
     *
     * @return list<string>
     */
    public static function ranks(): array
    {
        return array_keys(self::SCORE_TABLE['internasional']);
    }

    /**
     * Hitung agregat skor per kategori (akademis & non_akademis), masing-masing di-cap 50.
     *
     * Input adalah list entri sederhana:
     *   [['category' => 'akademis', 'level' => 'nasional', 'rank' => 'juara_2'], ...]
     * atau bisa juga sudah punya 'score' (akan dipakai langsung jika ada).
     *
     * @param  iterable<array{category:string, level?:string, rank?:string, score?:float|int}>  $entries
     * @return array{akademis: float, non_akademis: float}
     */
    public static function aggregateByCategory(iterable $entries): array
    {
        $sums = [self::CATEGORY_AKADEMIS => 0.0, self::CATEGORY_NON_AKADEMIS => 0.0];

        foreach ($entries as $entry) {
            $category = $entry['category'] ?? null;
            if (! array_key_exists($category, $sums)) {
                continue;
            }

            if (isset($entry['score'])) {
                $sums[$category] += (float) $entry['score'];
                continue;
            }

            $sums[$category] += self::scoreFor(
                $entry['level'] ?? '',
                $entry['rank'] ?? '',
            );
        }

        return [
            self::CATEGORY_AKADEMIS => min($sums[self::CATEGORY_AKADEMIS], self::AGGREGATE_CAP),
            self::CATEGORY_NON_AKADEMIS => min($sums[self::CATEGORY_NON_AKADEMIS], self::AGGREGATE_CAP),
        ];
    }
}
