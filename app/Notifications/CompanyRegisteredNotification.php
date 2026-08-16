<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to the super admin(s) whenever a company customer submits a registration
 * request, so they know there is a pending company account awaiting approval.
 */
class CompanyRegisteredNotification extends OperationNotification
{
    public function __construct(
        public string $companyOwnerName,
        public string $companyOwnerEmail,
        public ?string $companyName = null,
    ) {}

    protected function title(object $notifiable): string
    {
        return __('New company registration request');
    }

    protected function body(object $notifiable): string
    {
        return __('A new company account has requested to register and is pending your approval: :name (:email).', [
            'name' => $this->companyName ?: $this->companyOwnerName,
            'email' => $this->companyOwnerEmail,
        ]);
    }

    protected function type(): NotificationType
    {
        return NotificationType::WARNING;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('New company registration request'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('A new company account has requested to register on CarCarePlus and is pending approval.'));

        if ($this->companyName) {
            $mail->line(__('Company: :name', ['name' => $this->companyName]));
        }

        return $mail
            ->line(__('Owner: :name', ['name' => $this->companyOwnerName]))
            ->line(__('Email: :email', ['email' => $this->companyOwnerEmail]))
            ->line(__('Please review the request and approve or reject it.'));
    }
}
