<?php

use App\Http\Controllers\Admin\AchievementVerificationController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CriteriaController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\PendingStudentController;
use App\Http\Controllers\Admin\SelectionAuditController;
use App\Http\Controllers\Admin\SelectionController;
use App\Http\Controllers\Admin\SelectionHistoryController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ThresholdController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Mahasiswa\AchievementController as MahasiswaAchievementController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\ProfileController as MahasiswaProfileController;
use App\Http\Controllers\Mahasiswa\RankingController as MahasiswaRankingController;
use App\Http\Controllers\NotificationController;
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

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
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

        Route::get('/criteria', [CriteriaController::class, 'index'])->name('criteria.index');
        Route::put('/criteria/{fuzzySet}', [CriteriaController::class, 'update'])->name('criteria.update');

        Route::put('/threshold', [ThresholdController::class, 'update'])->name('threshold.update');

        Route::get('/selection/run', [SelectionController::class, 'create'])->name('selection.run');
        Route::post('/selection/run', [SelectionController::class, 'run']);
        Route::get('/selection/{batch}', [SelectionController::class, 'show'])->name('selection.show');
        Route::get('/selection/{batch}/progress', [SelectionController::class, 'progress'])->name('selection.progress');
        Route::get('/selection/{batch}/audit/{candidate}', [SelectionAuditController::class, 'show'])->name('selection.audit');

        Route::get('/selection/{batch}/export.csv', [ExportController::class, 'csv'])->name('selection.export.csv');
        Route::get('/selection/{batch}/export.pdf', [ExportController::class, 'pdf'])->name('selection.export.pdf');
        Route::get('/selection/{batch}/audit/{candidate}/export.pdf', [ExportController::class, 'auditPdf'])->name('selection.audit.export.pdf');

        Route::get('/history', [SelectionHistoryController::class, 'index'])->name('history.index');

        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity.index');

        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        Route::post('/achievements/{achievement}/verify', [AchievementVerificationController::class, 'verify'])
            ->name('achievements.verify');
        Route::post('/achievements/{achievement}/unverify', [AchievementVerificationController::class, 'unverify'])
            ->name('achievements.unverify');
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

        Route::get('/profile', [MahasiswaProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [MahasiswaProfileController::class, 'update'])->name('profile.update');

        Route::get('/ranking', [MahasiswaRankingController::class, 'index'])->name('ranking.index');

        Route::get('/achievements', [MahasiswaAchievementController::class, 'index'])->name('achievements.index');
        Route::post('/achievements', [MahasiswaAchievementController::class, 'store'])->name('achievements.store');
        Route::put('/achievements/{achievement}', [MahasiswaAchievementController::class, 'update'])->name('achievements.update');
        Route::delete('/achievements/{achievement}', [MahasiswaAchievementController::class, 'destroy'])->name('achievements.destroy');
    });
