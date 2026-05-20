<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Inferensi Tsukamoto:
 *  - α-predikat per rule = MIN(membership setiap antecedent)
 *  - z-output rule = fungsi monotonik dari α berdasarkan consequent + threshold
 *
 * Z-function (Tsukamoto Tsukamoto-flavoured weighted average):
 *  - layak           : z = threshold2 + α × (100 - threshold2)
 *  - dipertimbangkan : z = threshold1 + α × (threshold2 - threshold1)
 *  - tidak_layak     : z = threshold1 - α × threshold1
 */
final class InferenceEngine
{
    /**
     * @param  list<RuleSnapshot>  $rules
     * @return list<RuleEvaluation>
     */
    public function execute(
        MembershipMap $memberships,
        array $rules,
        ThresholdSnapshot $thresholds,
    ): array {
        $evaluations = [];

        foreach ($rules as $rule) {
            $alpha = $this->alphaOf($rule, $memberships);
            $z = $this->zOf($rule->consequent, $alpha, $thresholds);

            $evaluations[] = new RuleEvaluation(
                ruleCode: $rule->code,
                consequent: $rule->consequent,
                alpha: $alpha,
                z: $z,
                antecedents: $rule->antecedents,
            );
        }

        return $evaluations;
    }

    private function alphaOf(RuleSnapshot $rule, MembershipMap $memberships): float
    {
        $alpha = INF;

        foreach ($rule->antecedents as $criterion => $set) {
            $alpha = min($alpha, $memberships->get($criterion, $set));
        }

        return $alpha === INF ? 0.0 : $alpha;
    }

    private function zOf(string $consequent, float $alpha, ThresholdSnapshot $thresholds): float
    {
        return match ($consequent) {
            RuleSnapshot::CONSEQUENT_LAYAK => $thresholds->threshold2 + $alpha * (100.0 - $thresholds->threshold2),
            RuleSnapshot::CONSEQUENT_DIPERTIMBANGKAN => $thresholds->threshold1 + $alpha * ($thresholds->threshold2 - $thresholds->threshold1),
            RuleSnapshot::CONSEQUENT_TIDAK_LAYAK => $thresholds->threshold1 - $alpha * $thresholds->threshold1,
            default => throw new \InvalidArgumentException("Consequent tidak dikenal: {$consequent}"),
        };
    }
}
