<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\User;
use App\Models\Wallet;

/**
 * Sent to the super admin(s) whenever a staff member adjusts a customer's
 * wallet, recording who made the change, for whom, and by how much.
 */
class WalletAdjustedAdminNotification extends OperationNotification
{
    public function __construct(
        public Wallet $wallet,
        public ?User $admin,
        public float $amount,
        public ?string $note = null,
    ) {}

    protected function type(): NotificationType
    {
        return NotificationType::INFO;
    }

    protected function referenceType(): ?string
    {
        return 'wallet';
    }

    protected function referenceId(): ?int
    {
        return $this->wallet->id;
    }

    protected function title(object $notifiable): string
    {
        return __('A customer wallet was adjusted');
    }

    protected function body(object $notifiable): string
    {
        $verb = $this->amount >= 0 ? __('credited') : __('debited');

        $message = __('Admin #:adminId (:adminName) :verb customer #:customerId (:customerName) wallet by :amount. New balance: :balance.', [
            'adminId' => $this->admin?->id ?? '-',
            'adminName' => $this->admin?->name ?? __('system'),
            'verb' => $verb,
            'customerId' => $this->wallet->user_id,
            'customerName' => $this->wallet->user?->name ?? __('customer'),
            'amount' => number_format(abs($this->amount), 2),
            'balance' => number_format((float) $this->wallet->balance, 2),
        ]);

        if ($this->note) {
            $message .= ' ' . __('Note: :note', ['note' => $this->note]);
        }

        return $message;
    }
}
