<?php

use App\Models\FuzzySet;
use App\Models\OutputThreshold;
use App\Models\SelectionBatch;
use App\Models\User;
use App\Services\Selection\SelectionSnapshotService;
use Database\Seeders\CriteriaSeeder;
use Database\Seeders\OutputThresholdSeeder;
use Database\Seeders\RuleSeeder;

beforeEach(function () {
    $this->seed([CriteriaSeeder::class, RuleSeeder::class, OutputThresholdSeeder::class]);
});

it('snapshots fuzzy sets, rules, and thresholds at the time take() is called', function () {
    $admin = User::factory()->admin()->create();
    $batch = SelectionBatch::create([
        'label' => 'Snapshot Test',
        'triggered_by' => $admin->id,
        'status' => SelectionBatch::STATUS_QUEUED,
    ]);

    (new SelectionSnapshotService())->take($batch);
    $batch->refresh();

    expect($batch->snapshot_fuzzy_sets)->toBeArray()->toHaveCount(15); // 5 kriteria × 3 himpunan
    expect($batch->snapshot_rules)->toBeArray()->toHaveCount(75);
    expect($batch->snapshot_thresholds)->toBeArray()
        ->toHaveKey('threshold_1')
        ->toHaveKey('threshold_2');
});

it('keeps the snapshot of an older batch immutable when parameters change later', function () {
    $admin = User::factory()->admin()->create();

    $batchOld = SelectionBatch::create([
        'label' => 'Old',
        'triggered_by' => $admin->id,
        'status' => SelectionBatch::STATUS_COMPLETED,
    ]);
    (new SelectionSnapshotService())->take($batchOld);

    $oldSet = collect($batchOld->fresh()->snapshot_fuzzy_sets)
        ->firstWhere(fn ($s) => $s['criterion'] === 'ipk' && $s['name'] === 'sedang');
    $oldThreshold = $batchOld->fresh()->snapshot_thresholds['threshold_2'];

    // Mutate live state.
    /** @var FuzzySet $sedang */
    $sedang = FuzzySet::query()
        ->whereHas('criterion', fn ($q) => $q->where('code', 'ipk'))
        ->where('name', 'sedang')
        ->first();
    $sedang->forceFill(['a' => 3.30, 'b' => 3.55, 'c' => 3.80])->save();

    /** @var OutputThreshold $threshold */
    $threshold = OutputThreshold::query()->where('is_active', true)->orderByDesc('id')->first();
    $threshold->forceFill(['threshold_2' => 90])->save();

    // Snapshot batch lama tidak berubah.
    $batchOld->refresh();
    $stillOld = collect($batchOld->snapshot_fuzzy_sets)
        ->firstWhere(fn ($s) => $s['criterion'] === 'ipk' && $s['name'] === 'sedang');
    expect($stillOld)->toBe($oldSet);
    expect($batchOld->snapshot_thresholds['threshold_2'])->toBe($oldThreshold);

    // Snapshot batch baru ikut perubahan.
    $batchNew = SelectionBatch::create([
        'label' => 'New',
        'triggered_by' => $admin->id,
        'status' => SelectionBatch::STATUS_QUEUED,
    ]);
    (new SelectionSnapshotService())->take($batchNew);
    $batchNew->refresh();
    $newSet = collect($batchNew->snapshot_fuzzy_sets)
        ->firstWhere(fn ($s) => $s['criterion'] === 'ipk' && $s['name'] === 'sedang');
    expect((float) $newSet['b'])->toBe(3.55);
    expect((float) $batchNew->snapshot_thresholds['threshold_2'])->toBe(90.0);
});
