<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a company / workshop owner when the super admin rejects their request.
 */
class RegistrationRejectedNotification extends OperationNotification
{
    public function __construct(
        public string $accountType,
        public ?string $reason = null,
    ) {}

    protected function title(object $notifiable): string
    {
        return __('Update on your registration request');
    }

    protected function body(object $notifiable): string
    {
        $line = __('We are sorry to inform you that your :type registration was not approved.', ['type' => $this->accountType]);

        if ($this->reason) {
            $line .= ' '.__('Reason: :reason', ['reason' => $this->reason]);
        }

        return $line;
    }

    protected function type(): NotificationType
    {
        return NotificationType::ERROR;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('Update on your registration request'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('We are sorry to inform you that your :type registration was not approved.', ['type' => $this->accountType]));

        if ($this->reason) {
            $mail->line(__('Reason: :reason', ['reason' => $this->reason]));
        }

        return $mail->line(__('If you believe this is a mistake, please contact our support team.'));
    }
}