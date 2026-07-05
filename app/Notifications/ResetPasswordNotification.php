<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a user requests a password reset. Carries the reset token and a
 * deep link the frontend/mobile app can open to complete the reset.
 */
class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $base = rtrim(config('app.frontend_url', config('app.url')), '/');
        $url = $base.'/reset-password?token='.$this->token.'&email='.urlencode($notifiable->email);

        return (new MailMessage)
            ->subject(__('Reset your CarCarePlus password'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('Or use this reset code: :token', ['token' => $this->token]))
            ->line(__('This password reset link will expire in :count minutes.', ['count' => $expire]))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }
}
