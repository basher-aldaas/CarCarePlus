<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Enums\SparePartRequestStatus;
use App\Models\SparePartRequest;

/**
 * Sent to the technician who raised a spare-part change request once the
 * customer has approved or rejected it.
 */
class SparePartDecidedNotification extends OperationNotification
{
    public function __construct(public SparePartRequest $sparePartRequest) {}

    protected function type(): NotificationType
    {
        return $this->sparePartRequest->status === SparePartRequestStatus::APPROVED
            ? NotificationType::SUCCESS
            : NotificationType::WARNING;
    }

    protected function referenceType(): ?string
    {
        return 'spare_part_request';
    }

    protected function referenceId(): ?int
    {
        return $this->sparePartRequest->id;
    }

    protected function title(object $notifiable): string
    {
        return $this->sparePartRequest->status === SparePartRequestStatus::APPROVED
            ? __('Spare part request approved')
            : __('Spare part request rejected');
    }

    protected function body(object $notifiable): string
    {
        $this->sparePartRequest->loadMissing('material');

        $part = $this->sparePartRequest->material?->name;

        $message = $this->sparePartRequest->status === SparePartRequestStatus::APPROVED
            ? __('The customer approved your request to change the part ":part".', ['part' => $part])
            : __('The customer rejected your request to change the part ":part".', ['part' => $part]);

        if ($this->sparePartRequest->notes) {
            $message .= ' ' . __('Notes: :notes', ['notes' => $this->sparePartRequest->notes]);
        }

        return $message;
    }
}