<?php

use App\Models\SelectionBatch;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->mahasiswa()->create();
    Student::factory()->create([
        'user_id' => $this->student->id,
        'nim' => '210ACH001',
        'full_name' => $this->student->name,
        'ipk' => 3.5,
        'penghasilan_ortu' => 4_000_000,
        'tanggungan' => 3,
    ]);
});

it('rejects a sixth achievement entry', function () {
    $this->actingAs($this->student);

    $studentRow = $this->student->student;
    StudentAchievement::factory()->count(5)->create([
        'student_id' => $studentRow->id,
        'verified_by_admin' => false,
    ]);

    $this->from(route('mahasiswa.achievements.index'))
        ->post(route('mahasiswa.achievements.store'), [
            'title' => 'Lomba ekstra',
            'category' => 'akademis',
            'level' => 'nasional',
            'rank' => 'juara_3',
            'year' => 2025,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();
});

it('forbids editing or deleting a verified achievement', function () {
    $this->actingAs($this->student);
    $studentRow = $this->student->student;

    $a = StudentAchievement::factory()->create([
        'student_id' => $studentRow->id,
        'verified_by_admin' => true,
    ]);

    $this->put(route('mahasiswa.achievements.update', $a->id), [
        'title' => 'edit',
        'category' => 'akademis',
        'level' => 'nasional',
        'rank' => 'juara_2',
        'year' => 2025,
    ])->assertForbidden();

    $this->delete(route('mahasiswa.achievements.destroy', $a->id))->assertForbidden();
});

it('caps the akademis aggregate at 50', function () {
    $studentRow = $this->student->student;

    StudentAchievement::factory()->count(6)->create([
        'student_id' => $studentRow->id,
        'category' => 'akademis',
        'level' => 'internasional',
        'rank' => 'juara_1',
        'score' => 10,
        'verified_by_admin' => true,
    ]);

    expect((float) $studentRow->refresh()->agregat_akademis)->toBe(50.0);
});

it('rejects profile edit with HTTP 409 while a batch is running', function () {
    $admin = User::factory()->admin()->create();
    SelectionBatch::factory()->create([
        'status' => SelectionBatch::STATUS_RUNNING,
        'triggered_by' => $admin->id,
    ]);

    $this->actingAs($this->student);

    $studentRow = $this->student->student;

    $this->putJson(route('mahasiswa.profile.update'), [
        'full_name' => $studentRow->full_name,
        'semester' => 5,
        'ipk' => 3.6,
        'penghasilan_ortu' => 4_500_000,
        'tanggungan' => 3,
    ])->assertStatus(409);
});

it('allows profile edit when no batch is running', function () {
    $this->actingAs($this->student);
    $studentRow = $this->student->student;

    $this->put(route('mahasiswa.profile.update'), [
        'full_name' => 'Updated Name',
        'semester' => 5,
        'ipk' => 3.6,
        'penghasilan_ortu' => 4_500_000,
        'tanggungan' => 3,
    ])->assertRedirect();

    expect($studentRow->refresh()->full_name)->toBe('Updated Name');
});
