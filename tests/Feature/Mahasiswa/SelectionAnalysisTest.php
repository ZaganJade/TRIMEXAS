<?php

use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\SelectionRuleEvaluation;
use App\Models\Student;
use App\Models\User;
use App\Notifications\SelectionResultReadyNotification;
use Illuminate\Support\Facades\Notification;

it('notifies approved students when a selection batch completes', function () {
    Notification::fake();

    $admin = actingAsAdmin();

    $studentUser = User::factory()->mahasiswa()->create(['name' => 'Mhs A']);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'nim' => 'M001',
        'full_name' => 'Mhs A',
    ]);

    $batch = SelectionBatch::factory()->create([
        'triggered_by' => $admin->id,
        'status' => SelectionBatch::STATUS_RUNNING,
        'total_candidates' => 1,
        'processed_count' => 0,
        'total_eligible' => 0,
        'total_ineligible' => 0,
    ]);

    SelectionResult::create([
        'batch_id' => $batch->id,
        'student_id' => $student->id,
        'eligible' => true,
        'score' => 82.5,
        'category' => 'layak',
        'rank' => 1,
        'input_snapshot' => ['ipk' => 3.8],
    ]);

    $batch->forceFill([
        'processed_count' => 1,
        'total_eligible' => 1,
        'status' => SelectionBatch::STATUS_COMPLETED,
        'completed_at' => now(),
    ])->save();

    app(\App\Actions\Selection\NotifyStudentsOfBatchResultsAction::class)->execute($batch->refresh());

    Notification::assertSentTo(
        $studentUser->fresh(),
        SelectionResultReadyNotification::class,
        fn (SelectionResultReadyNotification $notification) => $notification->batch->id === $batch->id
            && $notification->result->student_id === $student->id,
    );
});

it('lets a student view their own analysis page for a completed batch', function () {
    $user = actingAsApprovedStudent();
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'nim' => 'M002',
        'full_name' => $user->name,
    ]);

    $batch = SelectionBatch::factory()->create([
        'status' => SelectionBatch::STATUS_COMPLETED,
        'completed_at' => now(),
        'snapshot_rules' => [[
            'code' => 'R001',
            'antecedents' => ['ipk' => 'tinggi'],
            'consequent' => 'layak',
        ]],
        'snapshot_thresholds' => ['threshold_1' => 50, 'threshold_2' => 75],
    ]);

    SelectionResult::create([
        'batch_id' => $batch->id,
        'student_id' => $student->id,
        'eligible' => true,
        'score' => 88.0,
        'category' => 'layak',
        'rank' => 2,
        'input_snapshot' => [
            'ipk' => 3.9,
            'penghasilan' => 3_000_000,
            'prestasi_akademis' => 20,
            'prestasi_non_akademis' => 10,
            'tanggungan' => 3,
            'memberships' => ['ipk' => ['tinggi' => 0.9]],
        ],
    ]);

    SelectionRuleEvaluation::create([
        'batch_id' => $batch->id,
        'student_id' => $student->id,
        'rule_code' => 'R001',
        'consequent' => 'layak',
        'alpha' => 0.75,
        'z' => 88.0,
    ]);

    $this->get(route('mahasiswa.analysis.show', $batch->id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Mahasiswa/Analysis')
            ->where('batch.id', $batch->id)
            ->where('candidate.nim', 'M002')
            ->where('result.score', 88)
            ->has('rules', 1));
});

it('returns an empty analysis page when the student has no completed results', function () {
    actingAsApprovedStudent();
    Student::factory()->create(['user_id' => auth()->id()]);

    $this->get(route('mahasiswa.analysis.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Mahasiswa/Analysis')
            ->where('batch', null)
            ->where('result', null));
});

it('forbids viewing analysis for a batch where the student has no result', function () {
    $user = actingAsApprovedStudent();
    Student::factory()->create(['user_id' => $user->id]);

    $batch = SelectionBatch::factory()->create([
        'status' => SelectionBatch::STATUS_COMPLETED,
        'completed_at' => now(),
    ]);

    $this->get(route('mahasiswa.analysis.show', $batch->id))
        ->assertNotFound();
});

it('includes a link to analysis in the selection result notification payload', function () {
    $batch = SelectionBatch::factory()->create([
        'label' => 'Batch Semester Genap',
        'status' => SelectionBatch::STATUS_COMPLETED,
    ]);

    $student = Student::factory()->create();

    $result = SelectionResult::create([
        'batch_id' => $batch->id,
        'student_id' => $student->id,
        'eligible' => true,
        'score' => 91.2,
        'category' => 'layak',
        'rank' => 1,
        'input_snapshot' => ['ipk' => 3.7],
    ]);

    $notification = new SelectionResultReadyNotification($batch, $result);
    $payload = $notification->toArray(User::factory()->mahasiswa()->make());

    expect($payload['type'])->toBe('selection_result_ready');
    expect($payload['action_url'])->toBe(route('mahasiswa.analysis.show', $batch));
    expect($payload['message'])->toContain('Batch Semester Genap');
});
