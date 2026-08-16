<?php

namespace App\Notifications;

use App\Notifications\Operations\OperationNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to the super admin(s) whenever a personal customer self-registers,
 * so they are informed a new customer joined the platform.
 */
class CustomerRegisteredNotification extends OperationNotification
{
    public function __construct(
        public string $customerName,
        public string $customerEmail,
    ) {}

    protected function title(object $notifiable): string
    {
        return __('New customer registration');
    }

    protected function body(object $notifiable): string
    {
        return __('A new customer has just registered on CarCarePlus: :name (:email).', [
            'name' => $this->customerName,
            'email' => $this->customerEmail,
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New customer registration'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('A new customer has just registered on CarCarePlus.'))
            ->line(__('Name: :name', ['name' => $this->customerName]))
            ->line(__('Email: :email', ['email' => $this->customerEmail]));
    }
}
