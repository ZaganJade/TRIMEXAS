<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'pending' => User::query()
                    ->where('role', User::ROLE_MAHASISWA)
                    ->where('approval_status', User::STATUS_PENDING)
                    ->count(),
                'active' => User::query()
                    ->where('role', User::ROLE_MAHASISWA)
                    ->where('approval_status', User::STATUS_APPROVED)
                    ->count(),
                'batch' => SelectionBatch::query()
                    ->where('status', SelectionBatch::STATUS_RUNNING)
                    ->count(),
                'total_selections' => SelectionBatch::query()
                    ->where('status', SelectionBatch::STATUS_COMPLETED)
                    ->count(),
            ],
            'recent_activities' => Activity::query()
                ->latest('id')
                ->with('causer:id,name,email')
                ->limit(5)
                ->get()
                ->map(fn (Activity $a) => [
                    'id' => $a->id,
                    'type' => $a->log_name,
                    'message' => $this->formatActivityMessage($a),
                    'time' => $a->created_at?->diffForHumans() ?? '-',
                    'event' => $a->event,
                ]),
        ]);
    }

    /**
     * Build a human-readable message from an activity log entry.
     */
    private function formatActivityMessage(Activity $activity): string
    {
        $causer = $activity->causer?->name ?? 'Sistem';
        $event = $activity->event;
        $subject = match ($activity->subject_type) {
            'App\\Models\\User' => 'pengguna',
            'App\\Models\\Student' => 'mahasiswa',
            'App\\Models\\SelectionBatch' => 'batch seleksi',
            'App\\Models\\Criterion' => 'kriteria',
            'App\\Models\\FuzzySet' => 'himpunan fuzzy',
            default => 'data',
        };

        return match ($event) {
            'created' => "{$causer} menambahkan {$subject} baru",
            'updated' => "{$causer} memperbarui {$subject}",
            'deleted' => "{$causer} menghapus {$subject}",
            default => "{$causer} melakukan aksi pada {$subject}",
        };
    }
}
