<?php

namespace App\Actions\Admin;

use App\Models\User;
use App\Notifications\AccountApprovedNotification;
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
            $previous = $student->approval_status;

            $student->forceFill([
                'approval_status' => User::STATUS_APPROVED,
                'rejection_reason' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ])->save();

            activity('user')
                ->causedBy($admin)
                ->performedOn($student)
                ->event('approved')
                ->withProperties([
                    'attributes' => ['approval_status' => User::STATUS_APPROVED],
                    'old' => ['approval_status' => $previous],
                ])
                ->log('Student approved');

            $student->refresh()->notify(new AccountApprovedNotification($admin));

            return $student->refresh();
        });
    }
}
