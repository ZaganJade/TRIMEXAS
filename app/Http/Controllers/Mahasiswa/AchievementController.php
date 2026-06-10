<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Domain\Achievement\AchievementScorer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\AchievementRequest;
use App\Models\StudentAchievement;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AchievementController extends Controller
{
    public function index(): Response
    {
        $student = $this->student();

        $achievements = StudentAchievement::query()
            ->where('student_id', $student->id)
            ->orderByDesc('year')
            ->get(['id', 'title', 'category', 'level', 'rank', 'year', 'score', 'verified_by_admin', 'certificate_path', 'certificate_original_name', 'certificate_size']);

        return Inertia::render('Mahasiswa/Achievements', [
            'achievements' => $achievements,
            'aggregate' => [
                'akademis' => $student->agregat_akademis,
                'non_akademis' => $student->agregat_non_akademis,
            ],
            'levels' => AchievementScorer::levels(),
            'ranks' => AchievementScorer::ranks(),
        ]);
    }

    public function store(AchievementRequest $request): RedirectResponse
    {
        $student = $this->student();

        $score = AchievementScorer::scoreFor($request->validated('level'), $request->validated('rank'));

        $data = $request->validated();
        unset($data['certificate']);

        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $path = $file->store("certificates/{$student->id}", 'public');
            $data['certificate_path'] = $path;
            $data['certificate_original_name'] = $file->getClientOriginalName();
            $data['certificate_size'] = $file->getSize();
        }

        StudentAchievement::create([
            ...$data,
            'student_id' => $student->id,
            'score' => $score,
        ]);

        return back()->with('success', 'Entri prestasi disimpan.');
    }

    public function update(AchievementRequest $request, StudentAchievement $achievement): RedirectResponse
    {
        $this->ensureOwnedAndUnverified($achievement);

        $score = AchievementScorer::scoreFor($request->validated('level'), $request->validated('rank'));

        $data = $request->validated();
        unset($data['certificate']);

        if ($request->hasFile('certificate')) {
            // Hapus file lama jika ada
            if ($achievement->certificate_path && \Storage::disk('public')->exists($achievement->certificate_path)) {
                \Storage::disk('public')->delete($achievement->certificate_path);
            }
            $file = $request->file('certificate');
            $path = $file->store("certificates/{$achievement->student_id}", 'public');
            $data['certificate_path'] = $path;
            $data['certificate_original_name'] = $file->getClientOriginalName();
            $data['certificate_size'] = $file->getSize();
        }

        $achievement->fill([
            ...$data,
            'score' => $score,
        ])->save();

        return back()->with('success', 'Entri prestasi diperbarui.');
    }

    public function destroy(StudentAchievement $achievement): RedirectResponse
    {
        $this->ensureOwnedAndUnverified($achievement);

        if ($achievement->certificate_path && \Storage::disk('public')->exists($achievement->certificate_path)) {
            \Storage::disk('public')->delete($achievement->certificate_path);
        }

        $achievement->delete();

        return back()->with('success', 'Entri prestasi dihapus.');
    }

    private function student()
    {
        $student = $this->request()->user()->student;
        abort_unless($student, 422, 'Profil mahasiswa tidak ditemukan.');

        return $student;
    }

    private function ensureOwnedAndUnverified(StudentAchievement $achievement): void
    {
        $student = $this->student();
        abort_unless($achievement->student_id === $student->id, 403, 'Akses ditolak.');

        if ($achievement->verified_by_admin) {
            abort(403, 'Entri yang sudah diverifikasi admin tidak dapat diubah.');
        }
    }

    private function request(): \Illuminate\Http\Request
    {
        return request();
    }
}
