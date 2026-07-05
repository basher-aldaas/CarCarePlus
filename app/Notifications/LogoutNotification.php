<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notice sent to a user when they sign out of their account.
 */
class LogoutNotification extends Notification implements ShouldQueue
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
            ->subject(__('You have signed out of CarCarePlus'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('You have successfully signed out of your CarCarePlus account.'))
            ->line(__('If this was not you, please sign in and change your password immediately.'));
    }
}
