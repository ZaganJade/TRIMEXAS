<?php

namespace App\Services\Selection;

use App\Models\Criterion;
use App\Models\OutputThreshold;
use App\Models\Rule;
use App\Models\SelectionBatch;

class SelectionSnapshotService
{
    /**
     * Salin state criteria + fuzzy_sets + rules (active=true) + output_thresholds
     * ke kolom JSONB di selection_batches sehingga ranking historis tidak terpengaruh
     * perubahan parameter.
     */
    public function take(SelectionBatch $batch): SelectionBatch
    {
        $fuzzySets = Criterion::query()
            ->with('fuzzySets')
            ->orderBy('display_order')
            ->get()
            ->flatMap(fn (Criterion $c) => $c->fuzzySets->map(fn ($s) => [
                'criterion' => $c->code,
                'name' => $s->name,
                'shape' => $s->shape,
                'a' => (float) $s->a,
                'b' => (float) $s->b,
                'c' => $s->c !== null ? (float) $s->c : null,
            ]))
            ->values()
            ->all();

        $rules = Rule::query()
            ->where('active', true)
            ->orderBy('code')
            ->get(['code', 'antecedents', 'consequent'])
            ->map(fn (Rule $r) => [
                'code' => $r->code,
                'antecedents' => $r->antecedents,
                'consequent' => $r->consequent,
            ])
            ->values()
            ->all();

        $threshold = OutputThreshold::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->firstOrFail();

        $batch->forceFill([
            'snapshot_fuzzy_sets' => $fuzzySets,
            'snapshot_rules' => $rules,
            'snapshot_thresholds' => [
                'threshold_1' => (float) $threshold->threshold_1,
                'threshold_2' => (float) $threshold->threshold_2,
            ],
        ])->save();

        return $batch->refresh();
    }
}
