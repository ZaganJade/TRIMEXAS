<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Fuzzifier: konversi crisp input -> derajat keanggotaan tiap himpunan.
 *
 * Hasil: MembershipMap dengan struktur:
 *   ['ipk' => ['rendah' => x, 'sedang' => y, 'tinggi' => z], ...]
 */
final class Fuzzifier
{
    /**
     * @param  list<FuzzySetSnapshot>  $fuzzySets Snapshot semua himpunan untuk semua kriteria.
     */
    public function fuzzify(CandidateInput $input, array $fuzzySets): MembershipMap
    {
        $crisp = $input->asCriteriaMap();
        $values = [];

        foreach ($fuzzySets as $set) {
            $criterion = $set->criterion;

            if (! array_key_exists($criterion, $crisp)) {
                continue;
            }

            $x = $crisp[$criterion];
            $values[$criterion][$set->name] = $this->compute($set, $x);
        }

        // Pastikan semua kriteria yang ada di crisp punya entry (walau kosong).
        foreach (array_keys($crisp) as $criterion) {
            $values[$criterion] ??= [];
        }

        return new MembershipMap($values);
    }

    private function compute(FuzzySetSnapshot $set, float $x): float
    {
        return match ($set->shape) {
            FuzzySetSnapshot::SHAPE_LINEAR_TURUN => MembershipFunctions::linearTurun($x, $set->a, $set->b),
            FuzzySetSnapshot::SHAPE_LINEAR_NAIK => MembershipFunctions::linearNaik($x, $set->a, $set->b),
            FuzzySetSnapshot::SHAPE_SEGITIGA => MembershipFunctions::segitiga(
                $x,
                $set->a,
                $set->b,
                $set->c ?? throw new \InvalidArgumentException(
                    "Himpunan segitiga {$set->criterion}.{$set->name} kekurangan parameter c."
                ),
            ),
            default => throw new \InvalidArgumentException(
                "Bentuk himpunan tidak dikenal: {$set->shape}"
            ),
        };
    }
}
