<?php

namespace App\Jobs;

use App\Domain\Achievement\AchievementScorer;
use App\Domain\Fuzzy\CandidateInput;
use App\Domain\Fuzzy\FuzzyEngine;
use App\Domain\Fuzzy\FuzzyResult;
use App\Domain\Fuzzy\SnapshotHydrator;
use App\Models\SelectionBatch;
use App\Models\Student;
use App\Models\User;
use App\Notifications\BatchCompletedNotification;
use App\Notifications\BatchFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessCandidateChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @param  list<int>  $studentIds
     */
    public function __construct(public int $batchId, public array $studentIds)
    {
    }

    public function handle(FuzzyEngine $engine): void
    {
        /** @var SelectionBatch $batch */
        $batch = SelectionBatch::query()->findOrFail($this->batchId);

        $snapshots = SnapshotHydrator::hydrate([
            'fuzzy_sets' => $batch->snapshot_fuzzy_sets ?? [],
            'rules' => $batch->snapshot_rules ?? [],
            'thresholds' => $batch->snapshot_thresholds ?? [
                'threshold_1' => 50,
                'threshold_2' => 75,
            ],
        ]);

        $students = Student::query()
            ->with(['user:id,approval_status', 'achievements:id,student_id,category,level,rank,score'])
            ->whereIn('id', $this->studentIds)
            ->get();

        $resultRows = [];
        $evalRows = [];
        $eligibleCount = 0;
        $ineligibleCount = 0;
        $now = now();

        foreach ($students as $student) {
            $aggregates = AchievementScorer::aggregateByCategory(
                $student->achievements->map(fn ($a) => [
                    'category' => $a->category,
                    'score' => (float) $a->score,
                ])->all(),
            );

            $candidate = new CandidateInput(
                candidateId: (string) $student->id,
                ipk: (float) $student->ipk,
                penghasilanOrtu: (float) $student->penghasilan_ortu,
                prestasiAkademis: $aggregates['akademis'],
                prestasiNonAkademis: $aggregates['non_akademis'],
                tanggungan: (float) $student->tanggungan,
                statusMahasiswa: (string) $student->status,
                semester: (int) $student->semester,
                approvalStatus: (string) ($student->user->approval_status ?? 'approved'),
            );

            $result = $engine->run($candidate, $snapshots);

            if ($result instanceof FuzzyResult) {
                // Lulus 4 gate (aktif, sem<=6, ipk>=3, approved) — dihitung fuzzy.
                // Kategori akhir 'tidak_layak' (skor < threshold_1) tetap dicatat
                // sebagai ineligible dengan alasan skor.
                $finalEligible = $result->category !== \App\Domain\Fuzzy\RuleSnapshot::CONSEQUENT_TIDAK_LAYAK;
                $threshold1 = (float) ($batch->snapshot_thresholds['threshold_1'] ?? 50);
                $threshold2 = (float) ($batch->snapshot_thresholds['threshold_2'] ?? 75);

                if ($finalEligible) {
                    $eligibleCount++;
                } else {
                    $ineligibleCount++;
                }

                $reasons = $finalEligible ? null : [
                    sprintf('Skor Z=%.2f di bawah threshold_1=%.2f', $result->score, $threshold1),
                    sprintf('Batas dipertimbangkan: threshold_1=%.2f, threshold_2=%.2f', $threshold1, $threshold2),
                ];

                $resultRows[] = [
                    'batch_id' => $batch->id,
                    'student_id' => $student->id,
                    'eligible' => $finalEligible,
                    'ineligibility_reasons' => $reasons !== null ? json_encode($reasons) : null,
                    'input_snapshot' => json_encode([
                        'ipk' => $candidate->ipk,
                        'penghasilan' => $candidate->penghasilanOrtu,
                        'prestasi_akademis' => $candidate->prestasiAkademis,
                        'prestasi_non_akademis' => $candidate->prestasiNonAkademis,
                        'tanggungan' => $candidate->tanggungan,
                        'memberships' => $result->memberships->toArray(),
                    ]),
                    'score' => $result->score,
                    'category' => $result->category,
                    'rank' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                foreach ($result->ruleEvaluations as $eval) {
                    if ($eval->alpha <= 0) {
                        continue; // simpan hanya rule yang fire untuk hemat space
                    }
                    $evalRows[] = [
                        'batch_id' => $batch->id,
                        'student_id' => $student->id,
                        'rule_code' => $eval->ruleCode,
                        'consequent' => $eval->consequent,
                        'alpha' => $eval->alpha,
                        'z' => $eval->z,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            } else {
                $ineligibleCount++;
                $ineligibleCount++;
                $resultRows[] = [
                    'batch_id' => $batch->id,
                    'student_id' => $student->id,
                    'eligible' => false,
                    'ineligibility_reasons' => json_encode($result->reasons),
                    'input_snapshot' => json_encode([
                        'ipk' => $candidate->ipk,
                        'penghasilan' => $candidate->penghasilanOrtu,
                        'prestasi_akademis' => $candidate->prestasiAkademis,
                        'prestasi_non_akademis' => $candidate->prestasiNonAkademis,
                        'tanggungan' => $candidate->tanggungan,
                    ]),
                    'score' => null,
                    'category' => null,
                    'rank' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($batch, $resultRows, $evalRows, $eligibleCount, $ineligibleCount): void {
            DB::table('selection_results')->insert($resultRows);
            if ($evalRows !== []) {
                DB::table('selection_rule_evaluations')->insert($evalRows);
            }

            $batch->forceFill([
                'processed_count' => $batch->processed_count + count($resultRows),
                'total_eligible' => $batch->total_eligible + $eligibleCount,
                'total_ineligible' => $batch->total_ineligible + $ineligibleCount,
            ])->save();

            // Mark batch completed when everyone is processed.
            $batch->refresh();
            if ($batch->processed_count >= $batch->total_candidates && $batch->status === SelectionBatch::STATUS_RUNNING) {
                $this->finalizeRanking($batch);
                $batch->forceFill([
                    'status' => SelectionBatch::STATUS_COMPLETED,
                    'completed_at' => now(),
                ])->save();

                $admin = User::query()->find($batch->triggered_by);
                if ($admin) {
                    $admin->notify(new BatchCompletedNotification($batch->refresh()));
                }
            }
        });
    }

    public function failed(\Throwable $e): void
    {
        SelectionBatch::query()->where('id', $this->batchId)->update([
            'status' => SelectionBatch::STATUS_FAILED,
            'error_summary' => json_encode([
                'message' => $e->getMessage(),
                'student_ids' => $this->studentIds,
            ]),
            'completed_at' => now(),
        ]);

        /** @var SelectionBatch|null $batch */
        $batch = SelectionBatch::query()->find($this->batchId);
        if ($batch) {
            $admin = User::query()->find($batch->triggered_by);
            if ($admin) {
                $admin->notify(new BatchFailedNotification($batch->refresh()));
            }
        }
    }

    private function finalizeRanking(SelectionBatch $batch): void
    {
        $eligible = DB::table('selection_results')
            ->where('batch_id', $batch->id)
            ->where('eligible', true)
            ->orderByDesc('score')
            ->orderBy('id')
            ->get(['id']);

        foreach ($eligible as $i => $row) {
            DB::table('selection_results')
                ->where('id', $row->id)
                ->update(['rank' => $i + 1]);
        }
    }
}
