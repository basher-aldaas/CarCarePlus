<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a company / workshop owner immediately after they submit a
 * registration request, acknowledging it is pending super-admin approval.
 */
class RegistrationPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $accountType  Localized label, e.g. "company" or "workshop".
     */
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
            ->subject(__('Registration request received'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('We have received your :type registration request.', ['type' => $this->accountType]))
            ->line(__('Your request is now pending review by our team.'))
            ->line(__('You will be notified by email once it has been approved.'));
    }
}
