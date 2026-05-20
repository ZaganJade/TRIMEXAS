<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Map derajat keanggotaan: criterion -> [setName => degree].
 *
 * Contoh:
 *   ['ipk' => ['rendah' => 0.0, 'sedang' => 0.6, 'tinggi' => 0.4], ...]
 */
final readonly class MembershipMap
{
    /**
     * @param  array<string, array<string, float>>  $values
     */
    public function __construct(public array $values)
    {
    }

    public function get(string $criterion, string $set): float
    {
        return $this->values[$criterion][$set] ?? 0.0;
    }

    /**
     * @return array<string, array<string, float>>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
