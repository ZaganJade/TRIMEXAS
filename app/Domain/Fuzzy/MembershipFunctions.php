<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Tiga fungsi keanggotaan murni (linear turun, segitiga, linear naik).
 *
 * Implementasi mengikuti definisi pada `docs/KnowledgeBase_RuleSpec.md` §2 dan
 * spec fuzzy-engine: nilai output ∈ [0, 1].
 *
 * Domain murni: tidak boleh `use Illuminate\*`.
 */
final class MembershipFunctions
{
    /**
     * Linear turun:
     *   x ≤ a           -> 1
     *   a < x < b       -> (b - x) / (b - a)
     *   x ≥ b           -> 0
     */
    public static function linearTurun(float $x, float $a, float $b): float
    {
        if ($a >= $b) {
            throw new \InvalidArgumentException(
                "linearTurun butuh a < b, dapat a={$a}, b={$b}."
            );
        }

        if ($x <= $a) {
            return 1.0;
        }

        if ($x >= $b) {
            return 0.0;
        }

        return ($b - $x) / ($b - $a);
    }

    /**
     * Segitiga:
     *   x ≤ a           -> 0
     *   a < x ≤ b       -> (x - a) / (b - a)
     *   b < x < c       -> (c - x) / (c - b)
     *   x ≥ c           -> 0
     */
    public static function segitiga(float $x, float $a, float $b, float $c): float
    {
        if (! ($a < $b && $b < $c)) {
            throw new \InvalidArgumentException(
                "segitiga butuh a < b < c, dapat a={$a}, b={$b}, c={$c}."
            );
        }

        if ($x <= $a || $x >= $c) {
            return 0.0;
        }

        if ($x === $b) {
            return 1.0;
        }

        if ($x < $b) {
            return ($x - $a) / ($b - $a);
        }

        return ($c - $x) / ($c - $b);
    }

    /**
     * Linear naik:
     *   x ≤ a           -> 0
     *   a < x < b       -> (x - a) / (b - a)
     *   x ≥ b           -> 1
     */
    public static function linearNaik(float $x, float $a, float $b): float
    {
        if ($a >= $b) {
            throw new \InvalidArgumentException(
                "linearNaik butuh a < b, dapat a={$a}, b={$b}."
            );
        }

        if ($x <= $a) {
            return 0.0;
        }

        if ($x >= $b) {
            return 1.0;
        }

        return ($x - $a) / ($b - $a);
    }
}
