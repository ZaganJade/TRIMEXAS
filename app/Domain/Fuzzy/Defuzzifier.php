<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Defuzzifikasi weighted average:
 *   Z = Σ(αᵢ × zᵢ) / Σ(αᵢ)
 *
 * Jika Σ(αᵢ) = 0 (no rule fires) → return 0.
 */
final class Defuzzifier
{
    /**
     * @param  list<RuleEvaluation>  $evaluations
     */
    public function weightedAverage(array $evaluations): float
    {
        $numerator = 0.0;
        $denominator = 0.0;

        foreach ($evaluations as $eval) {
            $numerator += $eval->alpha * $eval->z;
            $denominator += $eval->alpha;
        }

        if ($denominator <= 0.0) {
            return 0.0;
        }

        return $numerator / $denominator;
    }
}
