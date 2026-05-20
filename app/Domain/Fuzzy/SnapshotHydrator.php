<?php

namespace App\Domain\Fuzzy;

/**
 * Hydrate domain DTOs dari array snapshot (yang tersimpan di selection_batches).
 *
 * Domain murni — operasi array biasa, tidak menyentuh Eloquent.
 */
final class SnapshotHydrator
{
    /**
     * @param  array<int, array<string, mixed>>  $rawSets
     * @return list<FuzzySetSnapshot>
     */
    public static function hydrateFuzzySets(array $rawSets): array
    {
        return array_map(
            fn (array $s) => new FuzzySetSnapshot(
                criterion: (string) $s['criterion'],
                name: (string) $s['name'],
                shape: (string) $s['shape'],
                a: (float) $s['a'],
                b: (float) $s['b'],
                c: isset($s['c']) ? (float) $s['c'] : null,
            ),
            $rawSets,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rawRules
     * @return list<RuleSnapshot>
     */
    public static function hydrateRules(array $rawRules): array
    {
        return array_map(
            fn (array $r) => new RuleSnapshot(
                code: (string) $r['code'],
                antecedents: $r['antecedents'],
                consequent: (string) $r['consequent'],
            ),
            $rawRules,
        );
    }

    /**
     * @param  array{threshold_1: float|int|string, threshold_2: float|int|string}  $raw
     */
    public static function hydrateThresholds(array $raw): ThresholdSnapshot
    {
        return new ThresholdSnapshot(
            threshold1: (float) $raw['threshold_1'],
            threshold2: (float) $raw['threshold_2'],
        );
    }

    /**
     * @param  array<string, mixed>  $batchSnapshot Snapshot dari SelectionBatch (3 kunci JSONB).
     */
    public static function hydrate(array $batchSnapshot): EngineSnapshots
    {
        return new EngineSnapshots(
            fuzzySets: self::hydrateFuzzySets($batchSnapshot['fuzzy_sets'] ?? []),
            rules: self::hydrateRules($batchSnapshot['rules'] ?? []),
            thresholds: self::hydrateThresholds($batchSnapshot['thresholds']),
        );
    }
}
