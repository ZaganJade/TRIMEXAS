<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\SelectionRuleEvaluation;
use App\Models\Student;
use Inertia\Inertia;
use Inertia\Response;

class SelectionAuditController extends Controller
{
    public function show(SelectionBatch $batch, Student $candidate): Response
    {
        /** @var SelectionResult|null $result */
        $result = SelectionResult::query()
            ->where('batch_id', $batch->id)
            ->where('student_id', $candidate->id)
            ->first();

        abort_unless($result, 404, 'Kandidat tidak ditemukan dalam batch ini.');

        $evaluations = SelectionRuleEvaluation::query()
            ->where('batch_id', $batch->id)
            ->where('student_id', $candidate->id)
            ->orderByDesc('alpha')
            ->orderBy('rule_code')
            ->get(['rule_code', 'consequent', 'alpha', 'z']);

        return Inertia::render('Admin/Selection/Audit', [
            'batch' => [
                'id' => $batch->id,
                'label' => $batch->label,
            ],
            'candidate' => [
                'id' => $candidate->id,
                'nim' => $candidate->nim,
                'full_name' => $candidate->full_name,
            ],
            'result' => [
                'eligible' => (bool) $result->eligible,
                'score' => $result->score,
                'category' => $result->category,
                'rank' => $result->rank,
                'ineligibility_reasons' => $result->ineligibility_reasons,
                'input_snapshot' => $result->input_snapshot,
            ],
            'evaluations' => $evaluations,
        ]);
    }
}
