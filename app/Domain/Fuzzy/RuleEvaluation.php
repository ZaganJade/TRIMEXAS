<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Hasil evaluasi satu rule pada satu kandidat.
 */
final readonly class RuleEvaluation
{
    public function __construct(
        public string $ruleCode,
        public string $consequent,
        public float $alpha,
        public float $z,
        /** @var array<string, string> */
        public array $antecedents,
    ) {
    }
}
