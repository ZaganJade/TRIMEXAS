<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Parameter satu himpunan fuzzy. Bentuk:
 *  - linear_turun: gunakan a (titik mulai turun) dan b (turun penuh). c diabaikan.
 *  - segitiga    : a (mulai naik), b (puncak), c (turun penuh).
 *  - linear_naik : a (mulai naik), b (naik penuh). c diabaikan.
 */
final readonly class FuzzySetSnapshot
{
    public const SHAPE_LINEAR_TURUN = 'linear_turun';
    public const SHAPE_SEGITIGA = 'segitiga';
    public const SHAPE_LINEAR_NAIK = 'linear_naik';

    public function __construct(
        public string $criterion,
        public string $name,
        public string $shape,
        public float $a,
        public float $b,
        public ?float $c = null,
    ) {
    }
}
