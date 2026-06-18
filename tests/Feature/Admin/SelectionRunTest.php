<?php

use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\CriteriaSeeder;
use Database\Seeders\OutputThresholdSeeder;
use Database\Seeders\RuleSeeder;

beforeEach(function () {
    $this->seed([CriteriaSeeder::class, RuleSeeder::class, OutputThresholdSeeder::class]);
});

it('runs an end-to-end selection batch with 5 dummy candidates and ranks them', function () {
    $admin = actingAsAdmin();

    // 5 dummy approved students with varying profiles.
    $candidates = [
        ['nim' => 'C001', 'name' => 'A Top',     'ipk' => 3.95, 'penghasilan' => 2_500_000, 'tng' => 5, 'pa' => 30, 'pna' => 12],
        ['nim' => 'C002', 'name' => 'B Mid',     'ipk' => 3.55, 'penghasilan' => 4_500_000, 'tng' => 3, 'pa' => 18, 'pna' => 14],
        ['nim' => 'C003', 'name' => 'C Mid-Low', 'ipk' => 3.35, 'penghasilan' => 6_000_000, 'tng' => 2, 'pa' => 12, 'pna' => 10],
        ['nim' => 'C004', 'name' => 'D Low',     'ipk' => 3.70, 'penghasilan' => 12_000_000, 'tng' => 1, 'pa' => 4,  'pna' => 6],
        ['nim' => 'C005', 'name' => 'E Pending', 'ipk' => 3.80, 'penghasilan' => 3_500_000, 'tng' => 4, 'pa' => 22, 'pna' => 16, 'pending' => true],
    ];

    foreach ($candidates as $c) {
        $user = User::factory()
            ->state(['name' => $c['name'], 'email' => strtolower($c['nim']).'@test.local'])
            ->{($c['pending'] ?? false) ? 'pending' : 'mahasiswa'}()
            ->create();

        Student::factory()->create([
            'user_id' => $user->id,
            'nim' => $c['nim'],
            'full_name' => $c['name'],
            'semester' => 4,
            'status' => 'aktif',
            'ipk' => $c['ipk'],
            'penghasilan_ortu' => $c['penghasilan'],
            'tanggungan' => $c['tng'],
        ]);

        // Use precomputed achievement score (not enumerating real entries).
        DB::table('student_achievements')->insert([
            'student_id' => Student::where('nim', $c['nim'])->value('id'),
            'title' => 'Akademis dummy',
            'category' => 'akademis',
            'level' => 'nasional',
            'rank' => 'juara_2',
            'year' => 2025,
            'score' => $c['pa'],
            'verified_by_admin' => true,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('student_achievements')->insert([
            'student_id' => Student::where('nim', $c['nim'])->value('id'),
            'title' => 'Non-akademis dummy',
            'category' => 'non_akademis',
            'level' => 'nasional',
            'rank' => 'juara_2',
            'year' => 2025,
            'score' => $c['pna'],
            'verified_by_admin' => true,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Trigger run via controller (sync queue from phpunit.xml so jobs run inline).
    $response = $this->post('/admin/selection/run', ['label' => 'Test Batch']);
    $response->assertRedirect();

    $batch = SelectionBatch::query()->latest('id')->firstOrFail();
    expect($batch->status)->toBe(SelectionBatch::STATUS_COMPLETED);
    expect($batch->total_eligible)->toBe(2); // pending student excluded; low-scoring approved students can be ineligible
    expect($batch->total_ineligible)->toBe(2);

    // Ranking ordered by score desc.
    $ranks = SelectionResult::query()
        ->where('batch_id', $batch->id)
        ->where('eligible', true)
        ->orderBy('rank')
        ->with('student:id,nim,full_name')
        ->get();

    expect($ranks)->toHaveCount(2);
    expect($ranks->first()->student->nim)->toBe('C001'); // highest profile wins
    expect($ranks->pluck('rank')->all())->toBe([1, 2]);
});

it('progress endpoint returns the expected shape', function () {
    actingAsAdmin();

    $batch = SelectionBatch::factory()->create([
        'total_candidates' => 50,
        'processed_count' => 25,
        'status' => SelectionBatch::STATUS_RUNNING,
    ]);

    $this->getJson(route('admin.selection.progress', $batch->id))
        ->assertOk()
        ->assertJson([
            'status' => 'running',
            'total' => 50,
            'processed' => 25,
            'percentage' => 50,
        ]);
});
