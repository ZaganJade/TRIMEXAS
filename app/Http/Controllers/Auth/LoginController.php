<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/Login', [
            'canRegister' => true,
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->validated();
        /** @var User|null $user */
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Auth::validate($credentials)) {
            RateLimiter::hit($request->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if ($user->role === User::ROLE_MAHASISWA) {
            if ($user->approval_status === User::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda menunggu verifikasi admin.',
                ]);
            }

            if ($user->approval_status === User::STATUS_REJECTED) {
                $reason = $user->rejection_reason ?: 'Tidak ada alasan tersedia.';

                throw ValidationException::withMessages([
                    'email' => "Akun Anda ditolak. Alasan: {$reason}",
                ]);
            }
        }

        Auth::login($user, $request->boolean('remember'));
        RateLimiter::clear($request->throttleKey());
        $request->session()->regenerate();

        activity('auth')
            ->causedBy($user)
            ->event('login')
            ->log($user->isAdmin() ? 'Admin login' : 'Mahasiswa login');

        return redirect()->intended($this->dashboardRouteFor($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function dashboardRouteFor(User $user): string
    {
        return $user->isAdmin()
            ? route('admin.dashboard')
            : route('mahasiswa.dashboard');
    }
}
