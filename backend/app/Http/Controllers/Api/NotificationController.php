<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Notifications
 *
 * The authenticated user's in-app notifications (backed by Laravel's database
 * notification channel) plus mark-as-read actions.
 * @authenticated
 */
class NotificationController extends Controller
{
    /**
     * List notifications
     *
     * @queryParam filter string `unread` to return only unread notifications. Example: unread
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('filter') === 'unread'
            ? $request->user()->unreadNotifications()
            : $request->user()->notifications();

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $query->limit(50)->get(),
        ]);
    }

    /**
     * Mark one notification read
     *
     * @urlParam id string required The notification UUID. Example: 9b1f...
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    /**
     * Mark all read
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
