<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->limit(20)->get(['id', 'type', 'data', 'read_at', 'created_at']);
        $unread = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread' => $unread,
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'unread' => 0,
        ]);
    }
}
