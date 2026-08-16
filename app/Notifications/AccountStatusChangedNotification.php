<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a user when an admin activates or deactivates their account.
 */
class AccountStatusChangedNotification extends OperationNotification
{
    public function __construct(public bool $isActive) {}

    protected function title(object $notifiable): string
    {
        return $this->isActive
            ? __('Your account has been activated')
            : __('Your account has been deactivated');
    }

    protected function body(object $notifiable): string
    {
        return $this->isActive
            ? __('Your CarCarePlus account has been activated. You can now sign in and use the app.')
            : __('Your CarCarePlus account has been deactivated. You will not be able to sign in until it is reactivated. Please contact support if you believe this is a mistake.');
    }

    protected function type(): NotificationType
    {
        return $this->isActive ? NotificationType::SUCCESS : NotificationType::WARNING;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title($notifiable))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line($this->body($notifiable));

        if (! $this->isActive) {
            $mail->line(__('If you believe this is a mistake, please contact our support team.'));
        }

        return $mail;
    }
}
