<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_MAHASISWA,
                'approval_status' => User::STATUS_PENDING,
            ]);

            Student::create([
                'user_id' => $user->id,
                'nim' => $data['nim'],
                'full_name' => $data['name'],
                'semester' => $data['semester'],
                'status' => 'aktif',
                'ipk' => 0,
                'penghasilan_ortu' => 0,
                'tanggungan' => 0,
            ]);
        });

        return redirect()
            ->route('login')
            ->with('success', 'Pendaftaran berhasil. Akun Anda menunggu verifikasi admin.');
    }
}
