<?php

declare(strict_types=1);

namespace App\Domain\Fuzzy;

/**
 * Empat boolean gate sebelum kandidat masuk mesin fuzzy:
 *  1. status mahasiswa = "aktif"
 *  2. semester ≤ 6
 *  3. IPK ≥ 3.0
 *  4. akun = "approved"
 */
final class EligibilityChecker
{
    public function check(CandidateInput $input): EligibilityResult
    {
        $reasons = [];

        if ($input->statusMahasiswa !== 'aktif') {
            $reasons[] = "Status bukan aktif (sekarang: {$input->statusMahasiswa})";
        }

        if ($input->semester > 6) {
            $reasons[] = "Semester > 6";
        }

        if ($input->ipk < 3.0) {
            $reasons[] = "IPK < 3.0";
        }

        if ($input->approvalStatus !== 'approved') {
            $reasons[] = "Akun belum di-approve";
        }

        return new EligibilityResult(
            eligible: $reasons === [],
            reasons: $reasons,
        );
    }
}
