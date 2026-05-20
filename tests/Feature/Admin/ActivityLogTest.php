<?php

use App\Models\FuzzySet;
use App\Models\SelectionBatch;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\CriteriaSeeder;
use Database\Seeders\OutputThresholdSeeder;
use Database\Seeders\RuleSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed([CriteriaSeeder::class, RuleSeeder::class, OutputThresholdSeeder::class]);
});

it('logs an admin login', function () {
    $admin = User::factory()->admin()->create();

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect();

    $log = Activity::query()->where('log_name', 'auth')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->causer_id)->toBe($admin->id);
});

it('logs the approve student action', function () {
    $admin = actingAsAdmin();
    $student = User::factory()->pending()->create();

    $this->post(route('admin.students.approve', ['user' => $student->id]))->assertRedirect();

    $log = Activity::query()->where('log_name', 'user')->where('event', 'approved')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->causer_id)->toBe($admin->id);
    expect($log->subject_id)->toBe($student->id);
});

it('logs fuzzy set parameter updates', function () {
    actingAsAdmin();
    $set = FuzzySet::query()->where('shape', FuzzySet::SHAPE_SEGITIGA)->first();

    $this->put(route('admin.criteria.update', $set->id), [
        'a' => 3.30, 'b' => 3.50, 'c' => 3.70,
    ])->assertRedirect();

    $log = Activity::query()->where('log_name', 'fuzzy_set')->where('subject_id', $set->id)->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->properties['attributes'])->toBeArray();
});

it('logs a selection batch start', function () {
    $admin = actingAsAdmin();

    $this->post(route('admin.selection.run'), ['label' => 'M5 Test'])->assertRedirect();

    $log = Activity::query()->where('log_name', 'selection')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->causer_id)->toBe($admin->id);
    expect($log->properties['label'])->toBe('M5 Test');
});

it('logs an export action', function () {
    actingAsAdmin();
    $admin = User::factory()->admin()->create();
    $batch = SelectionBatch::factory()->create([
        'triggered_by' => $admin->id,
        'status' => SelectionBatch::STATUS_COMPLETED,
        'completed_at' => now(),
    ]);

    $this->get(route('admin.selection.export.csv', $batch->id))->assertOk();

    $log = Activity::query()->where('log_name', 'export')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->properties['format'])->toBe('csv');
});

it('exposes the activity log page to admin with filters', function () {
    actingAsAdmin();

    $this->get(route('admin.activity.index'))->assertOk();
    $this->get(route('admin.activity.index', ['log_name' => 'auth']))->assertOk();
});

it('does not expose a delete route for activity log', function () {
    actingAsAdmin();

    // No matching route should exist.
    $exists = collect(\Route::getRoutes())->contains(
        fn ($r) => str_contains($r->uri(), 'activity-log/{id}') && in_array('DELETE', $r->methods())
    );
    expect($exists)->toBeFalse();
});
