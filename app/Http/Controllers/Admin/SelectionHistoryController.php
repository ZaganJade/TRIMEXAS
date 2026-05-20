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
        $status = $request->query('status');

        $batches = SelectionBatch::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with('triggeredBy:id,name')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $batches->getCollection()->transform(fn (SelectionBatch $b) => [
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
            'status' => $status,
        ]);
    }
}
