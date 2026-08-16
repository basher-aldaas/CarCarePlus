<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a company / workshop owner when the super admin approves their request.
 */
class RegistrationApprovedNotification extends OperationNotification
{
    public function __construct(public string $accountType) {}

    protected function title(object $notifiable): string
    {
        return __('Your registration has been approved');
    }

    protected function body(object $notifiable): string
    {
        return __('Good news! Your :type registration has been approved and your account is now active.', ['type' => $this->accountType]);
    }

    protected function type(): NotificationType
    {
        return NotificationType::SUCCESS;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your registration has been approved'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('Good news! Your :type registration has been approved.', ['type' => $this->accountType]))
            ->line(__('Your account is now active and you can log in.'));
    }
}