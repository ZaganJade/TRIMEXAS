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
    public function create(Request $request): Response
    {
        // Batch history search & filter happen entirely client-side on the
        // Run page, so we ship the full (bounded) list up-front.
        $batches = SelectionBatch::query()
            ->select(['id', 'label', 'periode', 'tahun_akademik', 'status', 'created_at', 'total_candidates', 'total_eligible'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (SelectionBatch $b) => [
                'id' => $b->id,
                'label' => $b->label,
                'periode' => $b->periode,
                'tahun_akademik' => $b->tahun_akademik,
                'status' => $b->status,
                'total_candidates' => $b->total_candidates,
                'total_eligible' => $b->total_eligible,
                'created_at' => $b->created_at?->toIso8601String(),
            ]);

        // Hitung mahasiswa approved yang akan diproses
        $candidateCount = \App\Models\Student::query()
            ->whereHas('user', fn ($q) => $q->where('approval_status', \App\Models\User::STATUS_APPROVED))
            ->count();

        return Inertia::render('Admin/Selection/Run', [
            'batches' => $batches,
            'candidateCount' => $candidateCount,
            'filters' => [
                'q' => $request->input('q', ''),
                'batch_id' => $request->input('batch_id'),
            ],
            'periodeOptions' => [
                ['value' => 'ganjil', 'label' => 'Ganjil (Sept–Feb)'],
                ['value' => 'genap', 'label' => 'Genap (Mar–Agust)'],
            ],
        ]);
    }

    public function run(Request $request, SelectionSnapshotService $snapshots): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'periode' => ['nullable', 'in:ganjil,genap'],
            'tahun_akademik' => ['nullable', 'integer', 'min:2018', 'max:' . ((int) date('Y') + 1)],
        ], [
            'label.required' => 'Label batch wajib diisi.',
            'periode.in' => 'Periode harus ganjil atau genap.',
            'tahun_akademik.min' => 'Tahun akademik minimal 2018.',
        ]);

        $batch = SelectionBatch::create([
            'label' => $data['label'],
            'periode' => $data['periode'] ?? null,
            'tahun_akademik' => $data['tahun_akademik'] ?? null,
            'triggered_by' => $request->user()->id,
            'status' => SelectionBatch::STATUS_QUEUED,
        ]);

        $snapshots->take($batch);

        activity('selection')
            ->causedBy($request->user())
            ->performedOn($batch)
            ->withProperties(['label' => $batch->label])
            ->log('Selection batch started');

        // Local dev: jalankan job utama secara sinkron (chunk job akan dieksekusi
        // inline oleh ProcessSelectionBatchJob). Tidak butuh worker terpisah.
        // Production: dispatch ke queue 'seleksi' dan spawn worker via WorkerManager.
        if (app()->environment('local') && config('queue.default') === 'database') {
            ProcessSelectionBatchJob::dispatchSync($batch->id);
        } else {
            ProcessSelectionBatchJob::dispatch($batch->id)->onQueue('seleksi');
            // Self-spawn worker (Decision D2). Dijalankan setelah dispatch.
            WorkerManager::default(base_path())->ensureRunning();
        }

        return redirect()->route('admin.selection.show', $batch->id);
    }

    public function show(Request $request, SelectionBatch $batch): Response
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

        $batches = SelectionBatch::query()
            ->where('id', '!=', $batch->id)
            ->latest()
            ->limit(50)
            ->get(['id', 'label', 'periode', 'tahun_akademik'])
            ->map(fn (SelectionBatch $b) => [
                'id' => $b->id,
                'label' => $b->label,
                'periode' => $b->periode,
                'tahun_akademik' => $b->tahun_akademik,
            ]);

        return Inertia::render('Admin/Selection/Detail', [
            'batch' => [
                'id' => $batch->id,
                'label' => $batch->label,
                'periode' => $batch->periode,
                'tahun_akademik' => $batch->tahun_akademik,
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
            'batches' => $batches,
            'filters' => [
                'q' => $request->input('q', ''),
                'batch_id' => $request->input('batch_id'),
            ],
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
