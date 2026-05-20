<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Crisp input untuk seorang kandidat.
 *
 * Pure PHP DTO. Tidak boleh menyentuh Eloquent / Illuminate.
 */
final readonly class CandidateInput
{
    public function __construct(
        public string $candidateId,
        public float $ipk,
        public float $penghasilanOrtu,
        public float $prestasiAkademis,
        public float $prestasiNonAkademis,
        public float $tanggungan,
        public string $statusMahasiswa = 'aktif',
        public int $semester = 1,
        public string $approvalStatus = 'approved',
    ) {
    }

    /**
     * @return array<string, float>
     */
    public function asCriteriaMap(): array
    {
        return [
            'ipk' => $this->ipk,
            'penghasilan' => $this->penghasilanOrtu,
            'prestasi_akademis' => $this->prestasiAkademis,
            'prestasi_non_akademis' => $this->prestasiNonAkademis,
            'tanggungan' => $this->tanggungan,
        ];
    }
}
