<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PendingStudentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Guest auth routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/students/pending', [PendingStudentController::class, 'index'])
            ->name('students.pending');
        Route::post('/students/{user}/approve', [PendingStudentController::class, 'approve'])
            ->name('students.approve');
        Route::post('/students/{user}/reject', [PendingStudentController::class, 'reject'])
            ->name('students.reject');
    });

/*
|--------------------------------------------------------------------------
| Mahasiswa routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'approved'])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function (): void {
        Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    });
