<?php

namespace App\Notifications;

use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent when a user requests a password reset. Carries the reset token and a
 * deep link the frontend/mobile app can open to complete the reset.
 *
 * NOTE: the reset token is delivered by email only. The in-app record stores a
 * generic message and never persists the token/link.
 */
class ResetPasswordNotification extends OperationNotification
{
    public function __construct(public string $token) {}

    protected function title(object $notifiable): string
    {
        return __('Reset your CarCarePlus password');
    }

    protected function body(object $notifiable): string
    {
        return __('We received a password reset request for your account. A reset link has been sent to your email.');
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