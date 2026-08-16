<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Security notice sent to a user whenever a new sign-in to their account occurs.
 */
class LoginNotification extends OperationNotification
{
    public function __construct(
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $signedInAt = null,
    ) {}

    protected function title(object $notifiable): string
    {
        return __('New sign-in to your CarCarePlus account');
    }

    protected function body(object $notifiable): string
    {
        $line = __('We detected a new sign-in to your account.');

        if ($this->signedInAt) {
            $line .= ' '.__('Time: :time', ['time' => $this->signedInAt]);
        }
        if ($this->ip) {
            $line .= ' '.__('IP address: :ip', ['ip' => $this->ip]);
        }

        return $line;
    }

    protected function type(): NotificationType
    {
        return NotificationType::WARNING;
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