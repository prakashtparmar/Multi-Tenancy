<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * list of notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('Super Admin')) {
            $notifications = \Illuminate\Notifications\DatabaseNotification::where('type', 'App\Notifications\OrderNotification')
                ->latest()
                ->limit(10)
                ->get();
        } else {
            $notifications = $user->notifications()->latest()->limit(10)->get();
        }

        $mappedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'data' => $notification->data,
                'created_at' => $notification->created_at->diffForHumans(),
                'read_at' => $notification->read_at,
            ];
        });

        return response()->json($mappedNotifications);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('Super Admin')) {
            \Illuminate\Notifications\DatabaseNotification::where('type', 'App\Notifications\OrderNotification')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('Super Admin')) {
            $notification = \Illuminate\Notifications\DatabaseNotification::findOrFail($id);
        } else {
            $notification = $user->notifications()->findOrFail($id);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
}
