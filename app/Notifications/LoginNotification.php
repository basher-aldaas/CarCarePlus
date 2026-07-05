<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Security notice sent to a user whenever a new sign-in to their account occurs.
 */
class LoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $signedInAt = null,
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
            ->subject(__('New sign-in to your CarCarePlus account'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('We detected a new sign-in to your account.'));

        if ($this->signedInAt) {
            $mail->line(__('Time: :time', ['time' => $this->signedInAt]));
        }
        if ($this->ip) {
            $mail->line(__('IP address: :ip', ['ip' => $this->ip]));
        }
        if ($this->userAgent) {
            $mail->line(__('Device: :agent', ['agent' => $this->userAgent]));
        }

        return $mail
            ->line(__('If this was you, no action is needed.'))
            ->line(__('If you do not recognize this activity, please change your password immediately.'));
    }
}
