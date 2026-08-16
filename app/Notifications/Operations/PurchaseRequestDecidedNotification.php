<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;

/**
 * Sent to the branch admin who owns a purchase request once it has been
 * approved or rejected.
 */
class PurchaseRequestDecidedNotification extends OperationNotification
{
    public function __construct(public PurchaseRequest $purchaseRequest) {}

    protected function type(): NotificationType
    {
        return $this->purchaseRequest->status === PurchaseRequestStatus::APPROVED
            ? NotificationType::SUCCESS
            : NotificationType::WARNING;
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
        return $this->purchaseRequest->status === PurchaseRequestStatus::APPROVED
            ? __('Purchase request approved')
            : __('Purchase request rejected');
    }

    protected function body(object $notifiable): string
    {
        $id = $this->purchaseRequest->id;

        if ($this->purchaseRequest->status === PurchaseRequestStatus::APPROVED) {
            return __('Your purchase request #:id has been approved.', ['id' => $id]);
        }

        $message = __('Your purchase request #:id has been rejected.', ['id' => $id]);

        if ($this->purchaseRequest->rejection_reason) {
            $message .= ' ' . __('Reason: :reason', ['reason' => $this->purchaseRequest->rejection_reason]);
        }

        return $message;
    }
}