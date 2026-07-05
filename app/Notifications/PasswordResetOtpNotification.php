<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails a one-time password (OTP) code for resetting a password.
 */
class PasswordResetOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $code,
        public int $expiresInMinutes = 10,
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
        return (new MailMessage)
            ->subject(__('Your password reset code'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('Use the following code to reset your password:'))
            ->line($this->code)
            ->line(__('This code will expire in :count minutes.', ['count' => $this->expiresInMinutes]))
            ->line(__('If you did not request a password reset, please ignore this email.'));
    }
}
