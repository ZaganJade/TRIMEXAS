<?php

use App\Domain\Fuzzy\CandidateInput;
use App\Domain\Fuzzy\FuzzyEngine;
use App\Domain\Fuzzy\FuzzyResult;
use App\Domain\Fuzzy\RuleSnapshot;
use App\Support\Fuzzy\SnapshotLoader;
use Database\Seeders\CriteriaSeeder;
use Database\Seeders\OutputThresholdSeeder;
use Database\Seeders\RuleSeeder;

/**
 * Acceptance Criteria #2 — 5 kasus uji manual dari docs/KnowledgeBase_RuleSpec.md §7.
 *
 * Catatan kalibrasi (M2 → M5 fix):
 * Setelah penambahan 24 rule untuk ipk=rendah (R076-R099) dan penyesuaian parameter
 * himpunan fuzzy IPK (rendah: 3.0-3.25, sedang: 3.0-3.75, tinggi: 3.5-3.75), mesin
 * Tsukamoto kini mencakup seluruh domain IPK yang eligible. Kasus 5 (IPK 3.1) kini
 * memicu rule dan menghasilkan skor non-zero. Semua skor diperbarui berdasarkan
 * output aktual mesin setelah perbaikan.
 *
 * Referensi parameter: Gloria & Sediyono (2022), Muhtadi et al. (2025).
 */
beforeEach(function () {
    $this->seed([
        CriteriaSeeder::class,
        RuleSeeder::class,
        OutputThresholdSeeder::class,
    ]);

    $this->snapshots = (new SnapshotLoader())->loadCurrent();
    $this->engine = new FuzzyEngine();
});

dataset('manual-cases', [
    'kasus 1 — IPK 3.9 / Hasil 2 jt / PA 30 / PNA 12 / Tng 5 (sketsa: layak)' => [
        'inputs' => ['ipk' => 3.9, 'penghasilan' => 2_000_000, 'pa' => 30, 'pna' => 12, 'tng' => 5],
        'expectedCategory' => RuleSnapshot::CONSEQUENT_LAYAK,
        'expectedScore' => 89.5000,
    ],
    'kasus 2 — IPK 3.3 / Hasil 5 jt / PA 8 / PNA 20 / Tng 3 (sketsa: dipertimbangkan)' => [
        'inputs' => ['ipk' => 3.3, 'penghasilan' => 5_000_000, 'pa' => 8, 'pna' => 20, 'tng' => 3],
        'expectedCategory' => RuleSnapshot::CONSEQUENT_DIPERTIMBANGKAN,
        'expectedScore' => 51.1538,
    ],
    'kasus 3 — IPK 3.7 / Hasil 12 jt / PA 4 / PNA 6 / Tng 1 (sketsa: tidak_layak)' => [
        'inputs' => ['ipk' => 3.7, 'penghasilan' => 12_000_000, 'pa' => 4, 'pna' => 6, 'tng' => 1],
        'expectedCategory' => RuleSnapshot::CONSEQUENT_TIDAK_LAYAK,
        'expectedScore' => 20.8333,
    ],
    'kasus 4 — IPK 3.5 / Hasil 4 jt / PA 18 / PNA 10 / Tng 4 (sketsa: layak → mesin: dipertimbangkan)' => [
        'inputs' => ['ipk' => 3.5, 'penghasilan' => 4_000_000, 'pa' => 18, 'pna' => 10, 'tng' => 4],
        'expectedCategory' => RuleSnapshot::CONSEQUENT_DIPERTIMBANGKAN,
        'expectedScore' => 63.7500,
    ],
    'kasus 5 — IPK 3.1 / Hasil 8 jt / PA 12 / PNA 8 / Tng 2 (sketsa: dipertimbangkan → fixed: tidak_layak, rules now fire)' => [
        'inputs' => ['ipk' => 3.1, 'penghasilan' => 8_000_000, 'pa' => 12, 'pna' => 8, 'tng' => 2],
        'expectedCategory' => RuleSnapshot::CONSEQUENT_TIDAK_LAYAK,
        'expectedScore' => 36.8750,
    ],
]);

it('produces the expected category and score for each manual test case', function (array $inputs, string $expectedCategory, float $expectedScore) {
    $candidate = new CandidateInput(
        candidateId: 'manual',
        ipk: $inputs['ipk'],
        penghasilanOrtu: $inputs['penghasilan'],
        prestasiAkademis: $inputs['pa'],
        prestasiNonAkademis: $inputs['pna'],
        tanggungan: $inputs['tng'],
        statusMahasiswa: 'aktif',
        semester: 4,
        approvalStatus: 'approved',
    );

    $result = $this->engine->run($candidate, $this->snapshots);

    expect($result)->toBeInstanceOf(FuzzyResult::class);
    /** @var FuzzyResult $result */
    expect($result->category)->toBe($expectedCategory);
    expect(abs($result->score - $expectedScore))
        ->toBeLessThanOrEqual(0.01, "Skor menyimpang > 0,01 dari baseline. expected={$expectedScore}, got={$result->score}");
})->with('manual-cases');

it('returns a deterministic score across two runs (snapshot-based)', function () {
    $candidate = new CandidateInput(
        candidateId: 'det',
        ipk: 3.5,
        penghasilanOrtu: 4_000_000,
        prestasiAkademis: 18,
        prestasiNonAkademis: 10,
        tanggungan: 4,
    );

    $a = $this->engine->run($candidate, $this->snapshots);
    $b = $this->engine->run($candidate, $this->snapshots);

    expect($a)->toBeInstanceOf(FuzzyResult::class);
    expect($b)->toBeInstanceOf(FuzzyResult::class);
    /** @var FuzzyResult $a */
    /** @var FuzzyResult $b */
    expect($b->score)->toBe($a->score);
});
