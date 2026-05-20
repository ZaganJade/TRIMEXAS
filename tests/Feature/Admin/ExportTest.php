<?php

use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\SelectionRuleEvaluation;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $admin = User::factory()->admin()->create();
    $this->batch = SelectionBatch::factory()->create([
        'triggered_by' => $admin->id,
        'status' => SelectionBatch::STATUS_COMPLETED,
        'completed_at' => now(),
        'started_at' => now()->subMinutes(2),
        'total_candidates' => 3,
        'total_eligible' => 3,
        'processed_count' => 3,
    ]);

    foreach ([['Alice', 'X001', 88.5, 1], ['Bob', 'X002', 75.0, 2], ['Cici', 'X003', 60.0, 3]] as [$name, $nim, $score, $rank]) {
        $u = User::factory()->mahasiswa()->state(['name' => $name])->create();
        $s = Student::factory()->create(['user_id' => $u->id, 'nim' => $nim, 'full_name' => $name]);
        $r = SelectionResult::create([
            'batch_id' => $this->batch->id,
            'student_id' => $s->id,
            'eligible' => true,
            'score' => $score,
            'category' => $score >= 75 ? 'layak' : ($score >= 50 ? 'dipertimbangkan' : 'tidak_layak'),
            'rank' => $rank,
            'input_snapshot' => ['ipk' => 3.6],
        ]);
        SelectionRuleEvaluation::create([
            'batch_id' => $this->batch->id,
            'student_id' => $s->id,
            'rule_code' => 'R001',
            'consequent' => 'layak',
            'alpha' => 0.5,
            'z' => $score,
        ]);
    }
});

it('exports a CSV with the correct filename and UTF-8 BOM', function () {
    actingAsAdmin();

    $response = $this->get(route('admin.selection.export.csv', $this->batch->id));
    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $disposition = $response->headers->get('content-disposition');
    expect($disposition)->toContain('ranking_batch_'.$this->batch->id);
    expect($disposition)->toContain('.csv');

    ob_start();
    $response->sendContent();
    $body = ob_get_clean();

    expect(substr($body, 0, 3))->toBe("\xEF\xBB\xBF"); // BOM
    expect($body)->toContain('rank,nama,nim,skor,kategori,eligible');
    expect($body)->toContain('Alice');
    expect($body)->toContain('X001');
});

it('exports a ranking PDF with the correct filename', function () {
    actingAsAdmin();

    $response = $this->get(route('admin.selection.export.pdf', $this->batch->id));
    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('ranking_batch_'.$this->batch->id);
});

it('exports an audit PDF for a single candidate', function () {
    actingAsAdmin();

    $student = Student::query()->where('nim', 'X001')->firstOrFail();

    $response = $this->get(route('admin.selection.audit.export.pdf', [
        'batch' => $this->batch->id,
        'candidate' => $student->id,
    ]));
    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('audit_batch_'.$this->batch->id);
});

it('forbids mahasiswa from exporting CSV (HTTP 403)', function () {
    actingAsApprovedStudent();

    $this->get(route('admin.selection.export.csv', $this->batch->id))->assertForbidden();
});

it('redirects anonymous users from PDF export to /login', function () {
    $this->get(route('admin.selection.export.pdf', $this->batch->id))
        ->assertRedirect('/login');
});
