<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ApproveStudentAction;
use App\Actions\Admin\RejectStudentAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendingStudentController extends Controller
{
    public function index(): Response
    {
        $pending = User::query()
            ->where('role', User::ROLE_MAHASISWA)
            ->where('approval_status', User::STATUS_PENDING)
            ->with('student:id,user_id,nim,full_name,semester')
            ->orderBy('created_at')
            ->paginate(25)
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nim' => $user->student?->nim,
                'semester' => $user->student?->semester,
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Students/PendingList', [
            'pending' => $pending,
        ]);
    }

    public function approve(Request $request, User $user, ApproveStudentAction $action): RedirectResponse
    {
        $this->ensureMahasiswaPending($user);

        $action->execute($user, $request->user());

        return back()->with('success', "Akun {$user->name} disetujui.");
    }

    public function reject(Request $request, User $user, RejectStudentAction $action): RedirectResponse
    {
        $this->ensureMahasiswaPending($user);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        $action->execute($user, $request->user(), $data['reason']);

        return back()->with('success', "Akun {$user->name} ditolak.");
    }

    private function ensureMahasiswaPending(User $user): void
    {
        abort_unless(
            $user->role === User::ROLE_MAHASISWA && $user->approval_status === User::STATUS_PENDING,
            422,
            'Akun ini tidak dalam status menunggu verifikasi.'
        );
    }
}
