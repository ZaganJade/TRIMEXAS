<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Services\Selection\AuditViewDataBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalysisController extends Controller
{
    public function index(Request $request, AuditViewDataBuilder $builder): Response
    {
        $student = $request->user()->student;
        abort_unless($student, 403, 'Profil mahasiswa tidak ditemukan.');

        $latestResult = SelectionResult::query()
            ->where('student_id', $student->id)
            ->whereHas('batch', fn ($q) => $q->where('status', SelectionBatch::STATUS_COMPLETED))
            ->with('batch')
            ->latest('id')
            ->first();

        if (! $latestResult?->batch) {
            return Inertia::render('Mahasiswa/Analysis', [
                'batch' => null,
                'candidate' => null,
                'result' => null,
                'evaluations' => [],
                'rules' => [],
            ]);
        }

        return Inertia::render('Mahasiswa/Analysis', $builder->build($latestResult->batch, $student));
    }

    public function show(Request $request, SelectionBatch $batch, AuditViewDataBuilder $builder): Response
    {
        abort_unless($batch->status === SelectionBatch::STATUS_COMPLETED, 404, 'Batch belum selesai diproses.');

        $student = $request->user()->student;
        abort_unless($student, 403, 'Profil mahasiswa tidak ditemukan.');

        return Inertia::render('Mahasiswa/Analysis', $builder->build($batch, $student));
    }
}
