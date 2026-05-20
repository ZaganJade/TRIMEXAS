<?php

use App\Models\FuzzySet;
use App\Models\OutputThreshold;
use App\Models\User;
use Database\Seeders\CriteriaSeeder;
use Database\Seeders\OutputThresholdSeeder;

beforeEach(function () {
    $this->seed([CriteriaSeeder::class, OutputThresholdSeeder::class]);
});

it('lets admin update fuzzy set parameters with valid monotonic values', function () {
    actingAsAdmin();

    /** @var FuzzySet $set */
    $set = FuzzySet::query()->where('shape', FuzzySet::SHAPE_SEGITIGA)->first();

    $this->put(route('admin.criteria.update', $set->id), [
        'a' => 3.30,
        'b' => 3.50,
        'c' => 3.70,
    ])->assertRedirect();

    $set->refresh();
    expect((float) $set->a)->toBe(3.30);
    expect((float) $set->b)->toBe(3.50);
    expect((float) $set->c)->toBe(3.70);
});

it('rejects fuzzy set update when monotonicity fails (a < b < c)', function () {
    actingAsAdmin();

    $set = FuzzySet::query()->where('shape', FuzzySet::SHAPE_SEGITIGA)->first();

    $this->from(route('admin.criteria.index'))
        ->put(route('admin.criteria.update', $set->id), [
            'a' => 3.50,
            'b' => 3.25,
            'c' => 3.75,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();
});

it('rejects fuzzy set update when value is outside the criterion domain', function () {
    actingAsAdmin();

    /** @var FuzzySet $set */
    $set = FuzzySet::query()
        ->whereHas('criterion', fn ($q) => $q->where('code', 'ipk'))
        ->where('shape', FuzzySet::SHAPE_SEGITIGA)
        ->first();

    $this->from(route('admin.criteria.index'))
        ->put(route('admin.criteria.update', $set->id), [
            'a' => 3.25,
            'b' => 3.50,
            'c' => 5.00, // di luar domain IPK 3.00 - 4.00
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['c']);
});

it('updates the threshold values when both are inside [0,100] and t1 < t2', function () {
    actingAsAdmin();

    $this->put(route('admin.threshold.update'), [
        'threshold_1' => 55,
        'threshold_2' => 80,
    ])->assertRedirect();

    /** @var OutputThreshold $latest */
    $latest = OutputThreshold::query()->where('is_active', true)->orderByDesc('id')->first();
    expect((float) $latest->threshold_1)->toBe(55.0);
    expect((float) $latest->threshold_2)->toBe(80.0);
});

it('rejects threshold update when t1 >= t2', function () {
    actingAsAdmin();

    $this->from(route('admin.criteria.index'))
        ->put(route('admin.threshold.update'), [
            'threshold_1' => 80,
            'threshold_2' => 55,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['threshold_1']);
});

it('rejects threshold update when value is outside 0-100', function () {
    actingAsAdmin();

    $this->from(route('admin.criteria.index'))
        ->put(route('admin.threshold.update'), [
            'threshold_1' => 50,
            'threshold_2' => 120,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['threshold_2']);
});

it('forbids non-admin from updating fuzzy sets', function () {
    actingAsApprovedStudent();

    $set = FuzzySet::query()->first();

    $this->put(route('admin.criteria.update', $set->id), [
        'a' => 1, 'b' => 2, 'c' => 3,
    ])->assertForbidden();
});
