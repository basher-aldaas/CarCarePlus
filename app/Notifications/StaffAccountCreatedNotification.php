<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to an employee / admin when the super admin creates their account.
 * The password is chosen by the super admin, so this is an account-created
 * notice with the login email (not the password itself, for security).
 */
class StaffAccountCreatedNotification extends OperationNotification
{
    /**
     * @param  string  $accountType  Localized label, e.g. "employee" or "admin".
     */
    public function __construct(public string $accountType) {}

    protected function title(object $notifiable): string
    {
        return __('Your CarCarePlus account has been created');
    }

    protected function body(object $notifiable): string
    {
        return __('An :type account has been created for you on CarCarePlus. Please sign in with the password provided by the administrator.', ['type' => $this->accountType]);
    }

    protected function type(): NotificationType
    {
        return NotificationType::SUCCESS;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your CarCarePlus account has been created'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('An :type account has been created for you on CarCarePlus.', ['type' => $this->accountType]))
            ->line(__('Login email: :email', ['email' => $notifiable->email]))
            ->line(__('Please use the password provided to you by the administrator to sign in.'))
            ->line(__('For your security, we recommend changing your password after your first login.'));
    }
}