<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a personal customer right after a successful self-registration.
 *
 * Extends OperationNotification so it is delivered on both the in-app channel
 * (a row in the `notifications` table) and best-effort email.
 */
class WelcomeNotification extends OperationNotification
{
    protected function title(object $notifiable): string
    {
        return __('Welcome to CarCarePlus');
    }

    protected function body(object $notifiable): string
    {
        return __('Your account has been created successfully and is ready to use.');
    }

    protected function type(): NotificationType
    {
        return NotificationType::SUCCESS;
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
