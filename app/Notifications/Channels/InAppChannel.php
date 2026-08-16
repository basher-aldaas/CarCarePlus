<?php

namespace App\Notifications\Channels;

use App\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

/**
 * Persists a notification to the application's own `notifications` table
 * (the App\Models\Notification model) so the frontend can list/read them.
 *
 * Any notification routed through this channel must expose a `toInApp()`
 * method returning the row payload:
 *   ['title' => ..., 'body' => ..., 'type' => ..., 'reference_type' => ..., 'reference_id' => ...]
 */
class InAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toInApp')) {
            return;
        }

        // Only users are stored in-app (the table's user_id is required).
        $userId = $notifiable->getKey();

        if ($userId === null) {
            return;
        }

        $payload = $notification->toInApp($notifiable);

        NotificationModel::create([
            'user_id' => $userId,
            'title' => $payload['title'],
            'body' => $payload['body'],
            'type' => $payload['type'],
            'reference_type' => $payload['reference_type'] ?? null,
            'reference_id' => $payload['reference_id'] ?? null,
            'is_read' => false,
            'sent_via' => method_exists($notification, 'sentVia')
                ? $notification->sentVia()
                : $notification->via($notifiable),
        ]);
    }
}