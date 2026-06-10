<?php

namespace App\Jobs;

use App\Models\SelectionBatch;
use App\Models\Student;
use App\Models\User;
use App\Services\Selection\SelectionSnapshotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Job utama: dipanggil sekali per batch.
 *  1. Pastikan snapshot diambil (idempotent — aman jika sudah disnapshot).
 *  2. Hitung total kandidat eligible vs ineligible.
 *  3. Bagi kandidat eligible jadi chunk 50, dispatch ProcessCandidateChunkJob.
 *  4. Tulis hasil ineligible langsung tanpa queue tambahan.
 */
class ProcessSelectionBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(public int $batchId)
    {
    }

    public function handle(SelectionSnapshotService $snapshots): void
    {
        /** @var SelectionBatch $batch */
        $batch = SelectionBatch::query()->lockForUpdate()->findOrFail($this->batchId);

        if ($batch->status === SelectionBatch::STATUS_COMPLETED) {
            return;
        }

        if ($batch->snapshot_fuzzy_sets === null) {
            $snapshots->take($batch);
            $batch->refresh();
        }

        $batch->forceFill([
            'status' => SelectionBatch::STATUS_RUNNING,
            'started_at' => now(),
        ])->save();

        // Approved students saja — semua mahasiswa approved diproses (gate 4 rule menyaring di dalam engine).
        $studentIds = Student::query()
            ->whereHas('user', fn ($q) => $q->where('approval_status', User::STATUS_APPROVED))
            ->pluck('id')
            ->all();

        $batch->forceFill(['total_candidates' => count($studentIds)])->save();

        if ($studentIds === []) {
            $batch->forceFill([
                'status' => SelectionBatch::STATUS_COMPLETED,
                'completed_at' => now(),
            ])->save();

            return;
        }

        foreach (array_chunk($studentIds, 50) as $chunk) {
            // Local/dev: jalankan chunk secara sinkron agar tidak bergantung pada
            // queue worker yang sulit di-spawn dari HTTP request (khususnya di Windows/Laragon).
            // Production: tetap dispatch ke queue 'seleksi' untuk eksekusi terdistribusi.
            if (app()->environment('local') && config('queue.default') === 'database') {
                app(\App\Jobs\ProcessCandidateChunkJob::class, ['batchId' => $batch->id, 'studentIds' => $chunk])
                    ->handle(app(\App\Domain\Fuzzy\FuzzyEngine::class));
            } else {
                ProcessCandidateChunkJob::dispatch($batch->id, $chunk)->onQueue('seleksi');
            }
        }
    }
}
