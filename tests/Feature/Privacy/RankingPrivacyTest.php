<?php

use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();

    $this->batch = SelectionBatch::factory()->create([
        'triggered_by' => $this->admin->id,
        'status' => SelectionBatch::STATUS_COMPLETED,
        'completed_at' => now(),
        'total_candidates' => 3,
        'total_eligible' => 2,
        'total_ineligible' => 1,
        'processed_count' => 3,
    ]);

    // 2 eligible students.
    foreach ([['A First', 'C001', 88.0, 1], ['B Second', 'C002', 72.5, 2]] as [$name, $nim, $score, $rank]) {
        $u = User::factory()->mahasiswa()->state(['name' => $name])->create();
        $s = Student::factory()->create([
            'user_id' => $u->id,
            'nim' => $nim,
            'full_name' => $name,
            'penghasilan_ortu' => 4_000_000,
        ]);
        SelectionResult::create([
            'batch_id' => $this->batch->id,
            'student_id' => $s->id,
            'eligible' => true,
            'score' => $score,
            'category' => $score >= 75 ? 'layak' : 'dipertimbangkan',
            'rank' => $rank,
        ]);
    }
});

it('mahasiswa ranking page only exposes name, score, status', function () {
    $student = User::factory()->mahasiswa()->create();
    Student::factory()->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)->get(route('mahasiswa.ranking.index'));
    $response->assertOk();

    $rankings = $response->viewData('page')['props']['rankings'] ?? [];
    expect($rankings)->toBeArray()->toHaveCount(2);

    foreach ($rankings as $row) {
        expect(array_keys($row))->toBe(['name', 'score', 'status']);
        expect($row)->not->toHaveKey('nim');
        expect($row)->not->toHaveKey('penghasilan');
        expect($row)->not->toHaveKey('rank');
        expect($row)->not->toHaveKey('ineligibility_reasons');
        foreach (array_keys($row) as $key) {
            expect(str_starts_with((string) $key, 'audit_'))->toBeFalse();
        }
    }
});

it('mahasiswa dashboard only returns own data, never others scores', function () {
    // Create the student that has a result row, plus a different student.
    $owner = User::factory()->mahasiswa()->create();
    $ownerStudent = Student::factory()->create([
        'user_id' => $owner->id,
        'nim' => 'OWN1',
        'full_name' => 'Self Student',
    ]);
    SelectionResult::create([
        'batch_id' => $this->batch->id,
        'student_id' => $ownerStudent->id,
        'eligible' => true,
        'score' => 81.0,
        'category' => 'layak',
        'rank' => 3,
    ]);

    $response = $this->actingAs($owner)->get(route('mahasiswa.dashboard'));
    $response->assertOk();

    $props = $response->viewData('page')['props'];
    expect($props['myResult'])->toBeArray();
    expect((float) $props['myResult']['score'])->toBe(81.0);
    expect($props['myResult'])->not->toHaveKey('nim');
    expect($props['myResult'])->not->toHaveKey('penghasilan');

    // No other-student data leaked under any property.
    $serialized = json_encode($props);
    expect($serialized)->not->toContain('C001');
    expect($serialized)->not->toContain('C002');
});

it('mahasiswa cannot reach admin audit page', function () {
    $student = User::factory()->mahasiswa()->create();
    Student::factory()->create(['user_id' => $student->id]);

    $resultStudent = Student::query()->where('nim', 'C001')->first();

    $this->actingAs($student)
        ->get(route('admin.selection.audit', ['batch' => $this->batch->id, 'candidate' => $resultStudent->id]))
        ->assertForbidden();
});
