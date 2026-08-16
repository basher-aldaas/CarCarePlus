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

    /** Whether this request moves stock between branches (vs. an external purchase). */
    protected function isTransfer(): bool
    {
        return (bool) $this->purchaseRequest->request_type;
    }

    protected function title(object $notifiable): string
    {
        $approved = $this->purchaseRequest->status === PurchaseRequestStatus::APPROVED;

        if ($this->isTransfer()) {
            return $approved
                ? __('Stock transfer completed')
                : __('Stock transfer rejected');
        }

        return $approved
            ? __('Purchase request approved')
            : __('Purchase request rejected');
    }

    protected function body(object $notifiable): string
    {
        $id = $this->purchaseRequest->id;

        if ($this->purchaseRequest->status === PurchaseRequestStatus::APPROVED) {
            return $this->isTransfer()
                ? __('Your stock transfer #:id has been approved and the stock has been moved.', ['id' => $id])
                : __('Your purchase request #:id has been approved.', ['id' => $id]);
        }

        $message = $this->isTransfer()
            ? __('Your stock transfer #:id has been rejected.', ['id' => $id])
            : __('Your purchase request #:id has been rejected.', ['id' => $id]);

        if ($this->purchaseRequest->rejection_reason) {
            $message .= ' ' . __('Reason: :reason', ['reason' => $this->purchaseRequest->rejection_reason]);
        }

        return $message;
    }
}