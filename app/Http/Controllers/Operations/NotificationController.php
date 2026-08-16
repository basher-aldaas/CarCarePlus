<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Responses\Response;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Lets the authenticated user read and manage their own in-app notifications
 * (the rows written by App\Notifications\Channels\InAppChannel).
 *
 * Every action is scoped to the current user — a user can never see or touch
 * another user's notifications.
 */
class NotificationController extends Controller
{
    /**
     * List the current user's notifications, newest first.
     * Pass ?unread=1 to return only unread ones.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::query()
            ->where('user_id', $request->user()->id)
            ->latest('created_at');

        if ($request->boolean('unread')) {
            $query->where('is_read', false);
        }

        return Response::Success(
            data: NotificationResource::collection(
                $query->paginate($request->integer('per_page', 15))
            ),
            message: __('Notifications fetched successfully')
        );
    }

    /**
     * Show a single notification belonging to the current user.
     */
    public function show(Request $request, Notification $notification): JsonResponse
    {
        //$this->authorizeOwnership($request, $notification);

        return Response::Success(
            data: new NotificationResource($notification),
            message: __('Notification fetched successfully')
        );
    }

    /**
     * Number of unread notifications — handy for a badge count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return Response::Success(
            data: ['unread_count' => $count],
            message: __('Unread count fetched successfully')
        );
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $this->authorizeOwnership($request, $notification);

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return Response::Success(
            data: new NotificationResource($notification),
            message: __('Notification marked as read')
        );
    }

    /**
     * Mark every unread notification for the current user as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return Response::Success(
            data: [],
            message: __('All notifications marked as read')
        );
    }

    /**
     * Delete a single notification belonging to the current user.
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $this->authorizeOwnership($request, $notification);

        $notification->delete();

        return Response::Success(
            data: [],
            message: __('Notification deleted successfully')
        );
    }

    /**
     * Guard: the notification must belong to the requesting user.
     */
    private function authorizeOwnership(Request $request, Notification $notification): void
    {
        if ($notification->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException();
        }
    }
}
