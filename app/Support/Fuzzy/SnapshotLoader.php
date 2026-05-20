<?php

declare(strict_types=1);

namespace App\Support\Fuzzy;

use App\Domain\Fuzzy\EngineSnapshots;
use App\Domain\Fuzzy\FuzzySetSnapshot;
use App\Domain\Fuzzy\RuleSnapshot;
use App\Domain\Fuzzy\ThresholdSnapshot;
use App\Models\FuzzySet;
use App\Models\OutputThreshold;
use App\Models\Rule;

/**
 * Bridge Eloquent <-> Domain.
 *
 * Bertanggung jawab membaca state DB (criteria, fuzzy_sets, rules, output_thresholds)
 * dan mengembalikannya sebagai DTO domain murni.
 *
 * Domain layer tidak boleh `use Illuminate\*`; loader ini ada di luar domain
 * (App\Support\Fuzzy) sehingga aman bergantung pada Eloquent.
 */
final class SnapshotLoader
{
    public function loadCurrent(): EngineSnapshots
    {
        $fuzzySets = FuzzySet::query()
            ->with('criterion:id,code')
            ->get()
            ->map(fn (FuzzySet $set) => new FuzzySetSnapshot(
                criterion: $set->criterion->code,
                name: $set->name,
                shape: $set->shape,
                a: (float) $set->a,
                b: (float) $set->b,
                c: $set->c !== null ? (float) $set->c : null,
            ))
            ->values()
            ->all();

        $rules = Rule::query()
            ->where('active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Rule $rule) => new RuleSnapshot(
                code: $rule->code,
                antecedents: $rule->antecedents,
                consequent: $rule->consequent,
            ))
            ->values()
            ->all();

        $threshold = OutputThreshold::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        if (! $threshold) {
            throw new \RuntimeException('Tidak ada output_threshold aktif. Jalankan OutputThresholdSeeder.');
        }

        $thresholds = new ThresholdSnapshot(
            threshold1: (float) $threshold->threshold_1,
            threshold2: (float) $threshold->threshold_2,
        );

        return new EngineSnapshots(
            fuzzySets: $fuzzySets,
            rules: $rules,
            thresholds: $thresholds,
        );
    }
}
