<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;


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
