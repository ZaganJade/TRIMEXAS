<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = request()->user();
        $student = $user->student;

        $latestBatch = SelectionBatch::query()
            ->where('status', SelectionBatch::STATUS_COMPLETED)
            ->latest('completed_at')
            ->first();

        $myResult = null;
        if ($latestBatch && $student) {
            /** @var SelectionResult|null $row */
            $row = SelectionResult::query()
                ->where('batch_id', $latestBatch->id)
                ->where('student_id', $student->id)
                ->first();

            if ($row) {
                $myResult = $this->formatResult($row);
            }
        }

        $achievementsTotal = 0;
        $achievementsVerified = 0;
        $selectionCount = 0;
        $recentResults = [];
        $checklist = [];
        $profileCompletion = 0;

        if ($student) {
            $achievementsTotal = $student->achievements()->count();
            $achievementsVerified = $student->achievements()->where('verified_by_admin', true)->count();
            $selectionCount = SelectionResult::query()
                ->where('student_id', $student->id)
                ->count();

            $recentResults = SelectionResult::query()
                ->where('student_id', $student->id)
                ->with(['batch:id,label,completed_at'])
                ->latest('id')
                ->limit(3)
                ->get()
                ->map(function (SelectionResult $row) {
                    return [
                        'batch_id' => $row->batch_id,
                        'batch_label' => $row->batch?->label,
                        'completed_at' => $row->batch?->completed_at?->toIso8601String(),
                        ...$this->formatResult($row),
                    ];
                })
                ->values()
                ->all();

            $checklist = $this->buildChecklist($student, $achievementsTotal);
            $profileCompletion = (int) round(
                collect($checklist)->where('done', true)->count() / max(count($checklist), 1) * 100
            );
        }

        return Inertia::render('Mahasiswa/Dashboard', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'approval_status' => $user->approval_status,
            ],
            'student' => $student ? [
                'nim' => $student->nim,
                'full_name' => $student->full_name,
                'semester' => $student->semester,
                'ipk' => (float) $student->ipk,
            ] : null,
            'stats' => [
                'ipk' => $student ? (float) $student->ipk : null,
                'achievements_total' => $achievementsTotal,
                'achievements_verified' => $achievementsVerified,
                'selection_count' => $selectionCount,
                'profile_completion' => $profileCompletion,
            ],
            'checklist' => $checklist,
            'latestBatch' => $latestBatch ? [
                'id' => $latestBatch->id,
                'label' => $latestBatch->label,
                'completed_at' => $latestBatch->completed_at?->toIso8601String(),
            ] : null,
            'myResult' => $myResult,
            'recentResults' => $recentResults,
            'unreadNotifications' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatResult(SelectionResult $row): array
    {
        return [
            'eligible' => (bool) $row->eligible,
            'score' => $row->score !== null ? (float) $row->score : null,
            'status' => $row->category,
            'rank' => $row->rank,
            'reasons' => $row->ineligibility_reasons,
        ];
    }

    /**
     * @return list<array{key: string, label: string, done: bool, hint: string}>
     */
    private function buildChecklist(\App\Models\Student $student, int $achievementsTotal): array
    {
        return [
            [
                'key' => 'ipk',
                'label' => 'IPK terisi',
                'done' => (float) $student->ipk > 0,
                'hint' => 'Perbarui IPK terbaru di halaman profil.',
            ],
            [
                'key' => 'contact',
                'label' => 'Kontak & alamat',
                'done' => filled($student->phone) && filled($student->address),
                'hint' => 'Lengkapi nomor telepon dan alamat domisili.',
            ],
            [
                'key' => 'economy',
                'label' => 'Data ekonomi keluarga',
                'done' => (int) $student->penghasilan_ortu > 0 && (int) $student->tanggungan > 0,
                'hint' => 'Isi penghasilan orang tua dan jumlah tanggungan.',
            ],
            [
                'key' => 'achievements',
                'label' => 'Minimal satu prestasi',
                'done' => $achievementsTotal > 0,
                'hint' => 'Tambahkan prestasi akademis atau non-akademis.',
            ],
        ];
    }
}
