<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Snapshot satu rule yang aktif pada saat batch dijalankan.
 *
 * `antecedents` adalah map criterion -> setName, contoh:
 *   ['ipk' => 'tinggi', 'penghasilan' => 'rendah', ...]
 *
 * `consequent` ∈ { 'layak', 'dipertimbangkan', 'tidak_layak' }.
 */
final readonly class RuleSnapshot
{
    public const CONSEQUENT_LAYAK = 'layak';
    public const CONSEQUENT_DIPERTIMBANGKAN = 'dipertimbangkan';
    public const CONSEQUENT_TIDAK_LAYAK = 'tidak_layak';

    public function __construct(
        public string $code,
        /** @var array<string, string> */
        public array $antecedents,
        public string $consequent,
    ) {
    }
}
