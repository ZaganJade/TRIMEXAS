<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Hasil akhir mesin fuzzy untuk satu kandidat (eligible).
 */
final readonly class FuzzyResult
{
    /**
     * @param  list<RuleEvaluation>  $ruleEvaluations
     */
    public function __construct(
        public string $candidateId,
        public float $score,
        public string $category,
        public MembershipMap $memberships,
        public array $ruleEvaluations,
    ) {
    }
}
