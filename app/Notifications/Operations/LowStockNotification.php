<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;

/**
 * Sent to the branch admin when a material drops to (or below) its configured
 * minimum stock level. Scalar values are passed in so the message is stable
 * even if stock moves again before a queued send runs.
 */
class LowStockNotification extends OperationNotification
{
    public function __construct(
        public int $inventoryId,
        public ?string $materialName,
        public string $quantity,
        public string $min,
    ) {}

    protected function type(): NotificationType
    {
        return NotificationType::WARNING;
    }

    protected function referenceType(): ?string
    {
        return 'inventory';
    }

    protected function referenceId(): ?int
    {
        return $this->inventoryId;
    }

    protected function title(object $notifiable): string
    {
        return __('Low stock alert');
    }

    protected function body(object $notifiable): string
    {
        return __('The material ":material" has reached its minimum stock level (:quantity left, minimum :min). Please restock it.', [
            'material' => $this->materialName,
            'quantity' => $this->quantity,
            'min' => $this->min,
        ]);
    }
}