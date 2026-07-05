<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a company / workshop owner when the super admin rejects their request.
 */
class RegistrationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $accountType,
        public ?string $reason = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
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
