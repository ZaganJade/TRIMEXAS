<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Activity::query()
            ->latest('id')
            ->with('causer:id,name,email');

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->query('log_name'));
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', (int) $request->query('user_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }

        $logs = $query->paginate(50)->withQueryString();
        $logs->getCollection()->transform(fn (Activity $a) => [
            'id' => $a->id,
            'log_name' => $a->log_name,
            'description' => $a->description,
            'event' => $a->event,
            'subject_type' => $a->subject_type,
            'subject_id' => $a->subject_id,
            'causer' => $a->causer?->only(['id', 'name', 'email']),
            'properties' => $a->properties,
            'created_at' => $a->created_at?->toIso8601String(),
        ]);

        return Inertia::render('Admin/ActivityLog/Index', [
            'logs' => $logs,
            'filters' => [
                'log_name' => $request->query('log_name'),
                'user_id' => $request->query('user_id'),
                'from' => $request->query('from'),
                'to' => $request->query('to'),
            ],
            'logNames' => Activity::query()->select('log_name')->distinct()->pluck('log_name'),
            'users' => User::query()->where('role', User::ROLE_ADMIN)->get(['id', 'name']),
        ]);
    }
}
