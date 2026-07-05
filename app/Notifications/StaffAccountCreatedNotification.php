<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an employee / admin when the super admin creates their account.
 * The password is chosen by the super admin, so this is an account-created
 * notice with the login email (not the password itself, for security).
 */
class StaffAccountCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $accountType  Localized label, e.g. "employee" or "admin".
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
            ->subject(__('Your CarCarePlus account has been created'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('An :type account has been created for you on CarCarePlus.', ['type' => $this->accountType]))
            ->line(__('Login email: :email', ['email' => $notifiable->email]))
            ->line(__('Please use the password provided to you by the administrator to sign in.'))
            ->line(__('For your security, we recommend changing your password after your first login.'));
    }
}
