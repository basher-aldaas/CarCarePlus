<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Wraps Laravel's built-in mail channel so a mail failure (SMTP down,
 * rate-limited, bad credentials, …) is logged instead of thrown.
 *
 * Notifications are queued and deliver every channel inside a single job.
 * If the mail step throws, it aborts the whole job — which previously meant
 * the in-app row was never written and the job landed in `failed_jobs`.
 * Swallowing the mail error here keeps email best-effort and lets the other
 * channels (notably the in-app record) always run to completion.
 */
class SafeMailChannel
{
    public function __construct(private MailChannel $mail) {}

    public function send(object $notifiable, Notification $notification): void
    {
        try {
            $this->mail->send($notifiable, $notification);
        } catch (Throwable $e) {
            Log::warning('Notification mail delivery failed: '.$e->getMessage(), [
                'notification' => $notification::class,
                'notifiable_id' => method_exists($notifiable, 'getKey') ? $notifiable->getKey() : null,
            ]);
        }
    }
}