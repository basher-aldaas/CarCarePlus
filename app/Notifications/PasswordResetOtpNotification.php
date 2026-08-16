<?php

namespace App\Notifications;

use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Emails a one-time password (OTP) code for resetting a password.
 *
 * NOTE: the OTP code is delivered by email only. The in-app record stores a
 * generic message and never persists the code.
 */
class PasswordResetOtpNotification extends OperationNotification
{
    public function __construct(
        public string $code,
        public int $expiresInMinutes = 10,
    ) {}

    protected function title(object $notifiable): string
    {
        return __('Your password reset code');
    }

    protected function body(object $notifiable): string
    {
        return __('A password reset code has been sent to your email.');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your password reset code'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('Use the following code to reset your password:'))
            ->line($this->code)
            ->line(__('This code will expire in :count minutes.', ['count' => $this->expiresInMinutes]))
            ->line(__('If you did not request a password reset, please ignore this email.'));
    }
}
