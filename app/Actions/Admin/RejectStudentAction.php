<?php

namespace App\Actions\Admin;

use App\Models\User;
use App\Notifications\AccountRejectedNotification;
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
            $previous = $student->approval_status;

            $student->forceFill([
                'approval_status' => User::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ])->save();

            activity('user')
                ->causedBy($admin)
                ->performedOn($student)
                ->event('rejected')
                ->withProperties([
                    'attributes' => ['approval_status' => User::STATUS_REJECTED, 'rejection_reason' => $reason],
                    'old' => ['approval_status' => $previous],
                ])
                ->log('Student rejected');

            $student->refresh()->notify(new AccountRejectedNotification($reason));

            return $student->refresh();
        });
    }
}
