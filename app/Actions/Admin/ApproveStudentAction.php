<?php

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveStudentAction
{
    /**
     * Approve a pending student account.
     *
     * Side effects: updates approval columns and (in M4) dispatches notification.
     */
    public function execute(User $student, User $admin): User
    {
        if ($student->role !== User::ROLE_MAHASISWA) {
            throw new \DomainException('Hanya akun mahasiswa yang dapat di-approve.');
        }

        return DB::transaction(function () use ($student, $admin): User {
            $student->forceFill([
                'approval_status' => User::STATUS_APPROVED,
                'rejection_reason' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ])->save();

            // TODO M4 (8.5): dispatch AccountApprovedNotification.

            return $student->refresh();
        });
    }
}
