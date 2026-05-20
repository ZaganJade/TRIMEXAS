<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Hasil eligibility gate.
 */
final readonly class EligibilityResult
{
    /**
     * @param  list<string>  $reasons
     */
    public function __construct(
        public bool $eligible,
        public array $reasons = [],
    ) {
    }
}
