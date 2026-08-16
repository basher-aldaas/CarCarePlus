<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Wallet;

/**
 * Sent to the customer whose wallet balance was adjusted by staff.
 */
class WalletAdjustedNotification extends OperationNotification
{
    public function __construct(
        public Wallet $wallet,
        public float $amount,
        public ?string $note = null,
    ) {}

    protected function type(): NotificationType
    {
        return $this->amount >= 0 ? NotificationType::SUCCESS : NotificationType::WARNING;
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
        return $this->amount >= 0
            ? __('Your wallet was credited')
            : __('Your wallet was debited');
    }

    protected function body(object $notifiable): string
    {
        $message = $this->amount >= 0
            ? __('Your wallet was credited with :amount. Your new balance is :balance.', [
                'amount' => number_format(abs($this->amount), 2),
                'balance' => number_format((float) $this->wallet->balance, 2),
            ])
            : __('Your wallet was debited by :amount. Your new balance is :balance.', [
                'amount' => number_format(abs($this->amount), 2),
                'balance' => number_format((float) $this->wallet->balance, 2),
            ]);

        if ($this->note) {
            $message .= ' ' . __('Note: :note', ['note' => $this->note]);
        }

        return $message;
    }
}
