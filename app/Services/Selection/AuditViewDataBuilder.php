<?php

namespace App\Services\Selection;

use App\Models\Rule;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\SelectionRuleEvaluation;
use App\Models\Student;
use Illuminate\Support\Collection;

class AuditViewDataBuilder
{
    /**
     * @return array{
     *     batch: array<string, mixed>,
     *     candidate: array<string, mixed>,
     *     result: array<string, mixed>,
     *     evaluations: Collection<int, SelectionRuleEvaluation>,
     *     rules: list<array<string, mixed>>
     * }
     */
    public function build(SelectionBatch $batch, Student $candidate): array
    {
        /** @var SelectionResult|null $result */
        $result = SelectionResult::query()
            ->where('batch_id', $batch->id)
            ->where('student_id', $candidate->id)
            ->first();

        abort_unless($result, 404, 'Hasil seleksi tidak ditemukan untuk batch ini.');

        $evaluations = SelectionRuleEvaluation::query()
            ->where('batch_id', $batch->id)
            ->where('student_id', $candidate->id)
            ->orderByDesc('alpha')
            ->orderBy('rule_code')
            ->get(['rule_code', 'consequent', 'alpha', 'z']);

        $evalByCode = $evaluations->keyBy('rule_code');
        $snapshotRules = collect($batch->snapshot_rules ?? []);
        $ruleCodes = $snapshotRules->pluck('code')->filter()->values();

        $descriptions = $ruleCodes->isEmpty()
            ? collect()
            : Rule::query()->whereIn('code', $ruleCodes)->pluck('description', 'code');

        $rules = $snapshotRules
            ->map(function (array $rule) use ($evalByCode, $descriptions): array {
                $eval = $evalByCode->get($rule['code']);
                $alpha = $eval ? (float) $eval->alpha : 0.0;

                return [
                    'code' => $rule['code'],
                    'antecedents' => $rule['antecedents'] ?? [],
                    'consequent' => $rule['consequent'] ?? null,
                    'description' => $descriptions[$rule['code']] ?? null,
                    'alpha' => $alpha,
                    'z' => $eval ? (float) $eval->z : 0.0,
                    'fired' => $alpha > 0,
                ];
            })
            ->sort(function (array $a, array $b): int {
                if ($a['fired'] !== $b['fired']) {
                    return $b['fired'] <=> $a['fired'];
                }

                if ($a['fired']) {
                    $alphaCompare = $b['alpha'] <=> $a['alpha'];

                    return $alphaCompare !== 0 ? $alphaCompare : strcmp($a['code'], $b['code']);
                }

                return strcmp($a['code'], $b['code']);
            })
            ->values()
            ->all();

        return [
            'batch' => [
                'id' => $batch->id,
                'label' => $batch->label,
                'started_at' => $batch->started_at?->toIso8601String(),
                'completed_at' => $batch->completed_at?->toIso8601String(),
                'thresholds' => $batch->snapshot_thresholds ?? [],
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
            'rules' => $rules,
        ];
    }
}
