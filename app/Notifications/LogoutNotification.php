<?php

namespace App\Notifications;

use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notice sent to a user when they sign out of their account.
 */
class LogoutNotification extends OperationNotification
{
    protected function title(object $notifiable): string
    {
        return __('You have signed out of CarCarePlus');
    }

    protected function body(object $notifiable): string
    {
        return __('You have successfully signed out of your CarCarePlus account.');
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