<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureApprovedStudent;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'approved' => EnsureApprovedStudent::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request): string {
            $user = $request->user();

            if ($user instanceof User && $user->isAdmin()) {
                return route('admin.dashboard');
            }

            if ($user instanceof User && $user->isApprovedStudent()) {
                return route('mahasiswa.dashboard');
            }

            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi berakhir, silakan login ulang.'], 401);
            }

            return redirect()
                ->guest(route('login'))
                ->with('info', 'Sesi berakhir, silakan login ulang.');
        });
    })->create();
