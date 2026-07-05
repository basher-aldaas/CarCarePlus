<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a personal customer right after a successful self-registration.
 */
class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Welcome to CarCarePlus'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('Your account has been created successfully and is ready to use.'))
            ->line(__('Thank you for choosing CarCarePlus!'));
    }
}
