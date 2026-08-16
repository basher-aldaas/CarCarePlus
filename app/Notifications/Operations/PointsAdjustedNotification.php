<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Enums\PointsTransactionType;
use App\Models\PointsTransaction;

/**
 * Sent to the customer when staff add or deduct loyalty points from their
 * balance.
 */
class PointsAdjustedNotification extends OperationNotification
{
    public function __construct(
        public PointsTransaction $transaction,
        public ?string $note = null,
    ) {}

    protected function type(): NotificationType
    {
        return $this->transaction->type === PointsTransactionType::EARN
            ? NotificationType::SUCCESS
            : NotificationType::INFO;
    }

    protected function referenceType(): ?string
    {
        return 'points';
    }

    protected function referenceId(): ?int
    {
        return $this->transaction->id;
    }

    protected function title(object $notifiable): string
    {
        return $this->transaction->type === PointsTransactionType::EARN
            ? __('You received loyalty points')
            : __('Loyalty points redeemed');
    }

    protected function body(object $notifiable): string
    {
        $message = $this->transaction->type === PointsTransactionType::EARN
            ? __('You received :points points. Your new balance is :balance points.', [
                'points' => $this->transaction->points,
                'balance' => $this->transaction->balance_after,
            ])
            : __(':points points were redeemed from your account. Your new balance is :balance points.', [
                'points' => $this->transaction->points,
                'balance' => $this->transaction->balance_after,
            ]);

        if ($this->note) {
            $message .= ' ' . __('Note: :note', ['note' => $this->note]);
        }

        return $message;
    }
}
