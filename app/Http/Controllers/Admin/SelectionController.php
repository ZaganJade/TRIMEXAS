<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSelectionBatchJob;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Domain\Queue\WorkerManager;
use App\Services\Selection\SelectionSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SelectionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Admin/Selection/Run', [
            'lastBatches' => SelectionBatch::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'label', 'status', 'created_at'])
                ->map(fn (SelectionBatch $b) => [
                    'id' => $b->id,
                    'label' => $b->label,
                    'status' => $b->status,
                    'created_at' => $b->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function run(Request $request, SelectionSnapshotService $snapshots): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
        ], [
            'label.required' => 'Label batch wajib diisi.',
        ]);

        $batch = SelectionBatch::create([
            'label' => $data['label'],
            'triggered_by' => $request->user()->id,
            'status' => SelectionBatch::STATUS_QUEUED,
        ]);

        $snapshots->take($batch);

        activity('selection')
            ->causedBy($request->user())
            ->performedOn($batch)
            ->withProperties(['label' => $batch->label])
            ->log('Selection batch started');

        ProcessSelectionBatchJob::dispatch($batch->id)->onQueue('seleksi');

        // Self-spawn worker (Decision D2). Dijalankan setelah dispatch.
        WorkerManager::default(base_path())->ensureRunning();

        return redirect()->route('admin.selection.show', $batch->id);
    }

    public function show(SelectionBatch $batch): Response
    {
        $results = SelectionResult::query()
            ->where('batch_id', $batch->id)
            ->where('eligible', true)
            ->orderBy('rank')
            ->orderByDesc('score')
            ->with('student:id,nim,full_name')
            ->get()
            ->map(fn (SelectionResult $r) => [
                'rank' => $r->rank,
                'student_id' => $r->student_id,
                'nim' => $r->student?->nim,
                'name' => $r->student?->full_name,
                'score' => $r->score,
                'category' => $r->category,
            ]);

        $ineligible = SelectionResult::query()
            ->where('batch_id', $batch->id)
            ->where('eligible', false)
            ->with('student:id,nim,full_name')
            ->get()
            ->map(fn (SelectionResult $r) => [
                'student_id' => $r->student_id,
                'nim' => $r->student?->nim,
                'name' => $r->student?->full_name,
                'reasons' => $r->ineligibility_reasons,
            ]);

        return Inertia::render('Admin/Selection/Detail', [
            'batch' => [
                'id' => $batch->id,
                'label' => $batch->label,
                'status' => $batch->status,
                'total_candidates' => $batch->total_candidates,
                'total_eligible' => $batch->total_eligible,
                'total_ineligible' => $batch->total_ineligible,
                'processed_count' => $batch->processed_count,
                'started_at' => $batch->started_at?->toIso8601String(),
                'completed_at' => $batch->completed_at?->toIso8601String(),
            ],
            'results' => $results,
            'ineligible' => $ineligible,
        ]);
    }

    public function progress(SelectionBatch $batch): JsonResponse
    {
        $total = $batch->total_candidates;
        $processed = $batch->processed_count;
        $percentage = $total > 0 ? (int) round(($processed / $total) * 100) : 0;

        return response()->json([
            'status' => $batch->status,
            'total' => $total,
            'processed' => $processed,
            'percentage' => $percentage,
            'error_summary' => $batch->error_summary,
        ]);
    }
}
