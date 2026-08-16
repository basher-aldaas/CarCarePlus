<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to the super admin(s) whenever a workshop submits a registration
 * request, so they know there is a pending workshop account awaiting approval.
 */
class WorkshopRegisteredNotification extends OperationNotification
{
    public function __construct(
        public string $ownerName,
        public string $ownerEmail,
        public ?string $workshopName = null,
    ) {}

    protected function title(object $notifiable): string
    {
        return __('New workshop registration request');
    }

    protected function body(object $notifiable): string
    {
        return __('A new workshop account has requested to register and is pending your approval: :name (:email).', [
            'name' => $this->workshopName ?: $this->ownerName,
            'email' => $this->ownerEmail,
        ]);
    }

    protected function type(): NotificationType
    {
        return NotificationType::WARNING;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('New workshop registration request'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('A new workshop account has requested to register on CarCarePlus and is pending approval.'));

        if ($this->workshopName) {
            $mail->line(__('Workshop: :name', ['name' => $this->workshopName]));
        }

        return $mail
            ->line(__('Owner: :name', ['name' => $this->ownerName]))
            ->line(__('Email: :email', ['email' => $this->ownerEmail]))
            ->line(__('Please review the request and approve or reject it.'));
    }
}
