<?php

use App\Domain\Achievement\AchievementScorer;

it('returns the correct score for every (level x rank) combination', function (string $level, string $rank, float $expected) {
    expect(AchievementScorer::scoreFor($level, $rank))->toBe($expected);
})->with([
    // Internasional
    ['internasional', 'juara_1', 10.0],
    ['internasional', 'juara_2', 8.0],
    ['internasional', 'juara_3', 6.0],
    ['internasional', 'partisipasi', 4.0],
    // Nasional
    ['nasional', 'juara_1', 7.0],
    ['nasional', 'juara_2', 5.0],
    ['nasional', 'juara_3', 3.0],
    ['nasional', 'partisipasi', 2.0],
    // Provinsi
    ['provinsi', 'juara_1', 4.0],
    ['provinsi', 'juara_2', 3.0],
    ['provinsi', 'juara_3', 2.0],
    ['provinsi', 'partisipasi', 1.0],
    // Kabupaten
    ['kabupaten', 'juara_1', 2.0],
    ['kabupaten', 'juara_2', 1.5],
    ['kabupaten', 'juara_3', 1.0],
    ['kabupaten', 'partisipasi', 0.5],
]);

it('rejects an invalid (level x rank) combination', function () {
    expect(fn () => AchievementScorer::scoreFor('galaksi', 'juara_1'))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => AchievementScorer::scoreFor('internasional', 'pemenang'))
        ->toThrow(InvalidArgumentException::class);
});

it('aggregates entries per category', function () {
    $entries = [
        ['category' => 'akademis', 'level' => 'internasional', 'rank' => 'juara_1'], // 10
        ['category' => 'akademis', 'level' => 'nasional',      'rank' => 'juara_2'], // 5
        ['category' => 'akademis', 'level' => 'provinsi',      'rank' => 'partisipasi'], // 1
        ['category' => 'non_akademis', 'level' => 'kabupaten', 'rank' => 'juara_2'], // 1.5
    ];

    expect(AchievementScorer::aggregateByCategory($entries))
        ->toBe(['akademis' => 16.0, 'non_akademis' => 1.5]);
});

it('caps the aggregate at 50 per category', function () {
    $entries = array_fill(0, 6, [
        'category' => 'akademis',
        'level' => 'internasional',
        'rank' => 'juara_1', // 10
    ]);

    // 6 × 10 = 60 → capped to 50
    expect(AchievementScorer::aggregateByCategory($entries))
        ->toBe(['akademis' => 50.0, 'non_akademis' => 0.0]);
});

it('returns zero when there are no entries', function () {
    expect(AchievementScorer::aggregateByCategory([]))
        ->toBe(['akademis' => 0.0, 'non_akademis' => 0.0]);
});

it('uses precomputed score when entry already has a score field', function () {
    $entries = [
        ['category' => 'akademis', 'score' => 7.0],
        ['category' => 'non_akademis', 'score' => 3.5],
        ['category' => 'akademis', 'score' => 100.0], // way over cap
    ];

    expect(AchievementScorer::aggregateByCategory($entries))
        ->toBe(['akademis' => 50.0, 'non_akademis' => 3.5]);
});
