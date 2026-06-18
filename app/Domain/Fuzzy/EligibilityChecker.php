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
            $reasons[] = "Gugur pra-syarat: Status kemahasiswaan saat ini tidak aktif (sekarang: {$input->statusMahasiswa})";
        }

        if ($input->semester > 6) {
            $reasons[] = "Gugur pra-syarat: Semester pendaftar melebihi batas maksimal (Semester > 6)";
        }

        if ($input->ipk < 3.0) {
            $reasons[] = "Gugur pra-syarat: IPK di bawah standar minimal yang ditetapkan (IPK < 3.00)";
        }

        if ($input->approvalStatus !== 'approved') {
            $reasons[] = "Gugur pra-syarat: Akun pendaftar belum disetujui (Approved) oleh Admin";
        }

        return new EligibilityResult(
            eligible: $reasons === [],
            reasons: $reasons,
        );
    }
}
