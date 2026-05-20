<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = request()->user();
        $student = $user->student;

        $latestBatch = SelectionBatch::query()
            ->where('status', SelectionBatch::STATUS_COMPLETED)
            ->latest('completed_at')
            ->first();

        $myResult = null;
        if ($latestBatch && $student) {
            /** @var SelectionResult|null $row */
            $row = SelectionResult::query()
                ->where('batch_id', $latestBatch->id)
                ->where('student_id', $student->id)
                ->first();

            if ($row) {
                // PRIVACY: hanya kirim data milik mahasiswa ini.
                $myResult = [
                    'eligible' => (bool) $row->eligible,
                    'score' => $row->score !== null ? (float) $row->score : null,
                    'status' => $row->category,
                    'rank' => $row->rank,
                    'reasons' => $row->ineligibility_reasons,
                ];
            }
        }

        return Inertia::render('Mahasiswa/Dashboard', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'approval_status' => $user->approval_status,
            ],
            'latestBatch' => $latestBatch ? [
                'id' => $latestBatch->id,
                'label' => $latestBatch->label,
                'completed_at' => $latestBatch->completed_at?->toIso8601String(),
            ] : null,
            'myResult' => $myResult,
        ]);
    }
}
