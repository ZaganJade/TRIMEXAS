<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RankingController extends Controller
{
    public function index(Request $request): Response
    {
        $latest = SelectionBatch::query()
            ->where('status', SelectionBatch::STATUS_COMPLETED)
            ->latest('completed_at')
            ->first();

        if (! $latest) {
            return Inertia::render('Mahasiswa/Ranking', [
                'batchLabel' => null,
                'rankings' => [],
                'q' => null,
            ]);
        }

        $q = trim((string) $request->query('q', ''));

        $rankings = SelectionResult::query()
            ->where('batch_id', $latest->id)
            ->where('eligible', true)
            ->with('student:id,full_name')
            ->when($q !== '', fn ($qb) => $qb->whereHas(
                'student',
                fn ($s) => $s->where('full_name', 'like', '%'.$q.'%')
            ))
            ->orderBy('rank')
            ->limit(200)
            ->get()
            ->map(fn (SelectionResult $r) => [
                // PRIVACY (NFR-014): hanya 3 field publik.
                'name' => $r->student?->full_name,
                'score' => $r->score !== null ? (float) $r->score : null,
                'status' => $r->category,
            ])
            ->values();

        return Inertia::render('Mahasiswa/Ranking', [
            'batchLabel' => $latest->label,
            'rankings' => $rankings,
            'q' => $q !== '' ? $q : null,
        ]);
    }
}
