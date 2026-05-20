<?php

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectStudentAction
{
    /**
     * Reject a pending student account with a required reason.
     */
    public function execute(User $student, User $admin, string $reason): User
    {
        if ($student->role !== User::ROLE_MAHASISWA) {
            throw new \DomainException('Hanya akun mahasiswa yang dapat di-reject.');
        }

        $reason = trim($reason);
        if (mb_strlen($reason) < 10) {
            throw new \InvalidArgumentException('Alasan penolakan minimal 10 karakter.');
        }

        return DB::transaction(function () use ($student, $admin, $reason): User {
            $student->forceFill([
                'approval_status' => User::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ])->save();

            // TODO M4 (8.5): dispatch AccountRejectedNotification.

            return $student->refresh();
        });
    }
}
