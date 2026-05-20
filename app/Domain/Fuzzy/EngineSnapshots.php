<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Bundle snapshot yang dibutuhkan mesin fuzzy untuk satu batch.
 */
final readonly class EngineSnapshots
{
    /**
     * @param  list<FuzzySetSnapshot>  $fuzzySets
     * @param  list<RuleSnapshot>      $rules
     */
    public function __construct(
        public array $fuzzySets,
        public array $rules,
        public ThresholdSnapshot $thresholds,
    ) {
    }
}
