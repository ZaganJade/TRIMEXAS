<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Threshold output untuk kategorisasi.
 *  - score < threshold1            -> tidak_layak
 *  - threshold1 <= score < t2      -> dipertimbangkan
 *  - score >= threshold2           -> layak
 */
final readonly class ThresholdSnapshot
{
    public function __construct(
        public float $threshold1,
        public float $threshold2,
    ) {
        if ($threshold1 >= $threshold2) {
            throw new \InvalidArgumentException(
                "threshold1 ({$threshold1}) harus kurang dari threshold2 ({$threshold2})."
            );
        }
    }

    public function categorize(float $score): string
    {
        if ($score >= $this->threshold2) {
            return RuleSnapshot::CONSEQUENT_LAYAK;
        }

        if ($score >= $this->threshold1) {
            return RuleSnapshot::CONSEQUENT_DIPERTIMBANGKAN;
        }

        return RuleSnapshot::CONSEQUENT_TIDAK_LAYAK;
    }
}
