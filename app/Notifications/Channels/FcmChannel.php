<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

/**
 * Sends a push notification through Firebase Cloud Messaging (FCM).
 *
 * The actual delivery is COMMENTED OUT for now — the frontend team will wire
 * Firebase up later. Until then this channel is a safe no-op: nothing is sent,
 * nothing breaks. To enable it:
 *   1. Add the FCM_* / firebase credentials to your .env and config/services.php.
 *   2. Uncomment the send() body below.
 *   3. Add FcmChannel::class to the notification's channel list
 *      (see OperationNotification::channels()).
 *
 * Any notification routed here must expose a `toFcm()` method returning:
 *   ['title' => ..., 'body' => ..., 'data' => [...]]
 */
class FcmChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        // The device tokens this notification should be pushed to.
        $tokens = $notifiable->routeNotificationFor('fcm', $notification);

        if (empty($tokens)) {
            return;
        }

        $message = $notification->toFcm($notifiable);

        // ---------------------------------------------------------------------
        // TODO(firebase): uncomment once Firebase Cloud Messaging is configured.
        //
        // Example using the FCM HTTP v1 API via Laravel's HTTP client. Replace
        // the project id / access token wiring with your real credentials
        // (e.g. a google service account) and adjust the payload as needed.
        //
        // $projectId = config('services.firebase.project_id');
        //
        // foreach ((array) $tokens as $token) {
        //     \Illuminate\Support\Facades\Http::withToken($this->accessToken())
        //         ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
        //             'message' => [
        //                 'token' => $token,
        //                 'notification' => [
        //                     'title' => $message['title'] ?? null,
        //                     'body' => $message['body'] ?? null,
        //                 ],
        //                 'data' => array_map('strval', $message['data'] ?? []),
        //             ],
        //         ]);
        // }
        // ---------------------------------------------------------------------
    }

    // TODO(firebase): implement OAuth2 access-token retrieval for the FCM v1 API
    // (e.g. via a google/apiclient service account) when enabling this channel.
    //
    // protected function accessToken(): string
    // {
    //     // return ...;
    // }
}