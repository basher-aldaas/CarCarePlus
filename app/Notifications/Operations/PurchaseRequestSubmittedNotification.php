<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\PurchaseRequest;

/**
 * Sent to the approver(s) — super admins and the branch admin — when a new
 * purchase (or transfer) request is submitted and awaits a decision.
 */
class PurchaseRequestSubmittedNotification extends OperationNotification
{
    public function __construct(public PurchaseRequest $purchaseRequest) {}

    protected function type(): NotificationType
    {
        return NotificationType::INFO;
    }

    protected function referenceType(): ?string
    {
        return 'purchase_request';
    }

    protected function referenceId(): ?int
    {
        return $this->purchaseRequest->id;
    }

    protected function title(object $notifiable): string
    {
        return __('New purchase request');
    }

    protected function body(object $notifiable): string
    {
        $this->purchaseRequest->loadMissing('branch');

        return __('Purchase request #:id from branch ":branch" is awaiting your review.', [
            'id' => $this->purchaseRequest->id,
            'branch' => $this->purchaseRequest->branch?->name,
        ]);
    }
}