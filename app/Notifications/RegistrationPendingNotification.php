<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a company / workshop owner immediately after they submit a
 * registration request, acknowledging it is pending super-admin approval.
 */
class RegistrationPendingNotification extends OperationNotification
{
    /**
     * @param  string  $accountType  Localized label, e.g. "company" or "workshop".
     */
    public function __construct(public string $accountType) {}

    protected function title(object $notifiable): string
    {
        return __('Registration request received');
    }

    protected function body(object $notifiable): string
    {
        return __('We have received your :type registration request. It is now pending review by our team.', ['type' => $this->accountType]);
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
