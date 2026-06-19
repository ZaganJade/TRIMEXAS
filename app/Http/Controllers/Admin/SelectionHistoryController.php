<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SelectionHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        // The history view uses a client-side data table (search, filter,
        // sort, pagination all happen in the browser), so we ship the full
        // batch list up-front. Batch rounds are a small, bounded set.
        $batches = SelectionBatch::query()
            ->with('triggeredBy:id,name')
            ->latest()
            ->get()
            ->map(fn (SelectionBatch $b) => [
                'id' => $b->id,
                'label' => $b->label,
                'status' => $b->status,
                'triggered_by' => $b->triggeredBy?->name,
                'created_at' => $b->created_at?->toIso8601String(),
                'completed_at' => $b->completed_at?->toIso8601String(),
                'total_candidates' => $b->total_candidates,
                'total_eligible' => $b->total_eligible,
            ]);

        return Inertia::render('Admin/History', [
            'batches' => $batches,
            'status' => $request->query('status'),
        ]);
    }
}
