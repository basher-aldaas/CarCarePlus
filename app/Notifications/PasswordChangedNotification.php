<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Confirmation sent after a user's password is changed — via reset or while
 * signed in. Acts as a security alert if the change was not made by the user.
 */
class PasswordChangedNotification extends OperationNotification
{
    protected function title(object $notifiable): string
    {
        return __('Your CarCarePlus password was changed');
    }

    protected function body(object $notifiable): string
    {
        return __('This is a confirmation that the password for your account has just been changed. If you did not make this change, please contact our support team immediately.');
    }

    protected function type(): NotificationType
    {
        return NotificationType::WARNING;
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