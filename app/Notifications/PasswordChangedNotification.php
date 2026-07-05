<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation sent after a user's password is changed — via reset or while
 * signed in. Acts as a security alert if the change was not made by the user.
 */
class PasswordChangedNotification extends Notification implements ShouldQueue
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
            ->subject(__('Your CarCarePlus password was changed'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('This is a confirmation that the password for your account has just been changed.'))
            ->line(__('If you made this change, no further action is needed.'))
            ->line(__('If you did NOT change your password, please contact our support team immediately.'));
    }
}
