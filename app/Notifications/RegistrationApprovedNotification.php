<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a company / workshop owner when the super admin approves their request.
 */
class RegistrationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $accountType) {}

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
            ->subject(__('Your registration has been approved'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('Good news! Your :type registration has been approved.', ['type' => $this->accountType]))
            ->line(__('Your account is now active and you can log in.'));
    }
}
