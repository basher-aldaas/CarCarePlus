<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\SparePartRequest;

/**
 * Sent to the customer when a technician requests to change a spare part on
 * their order, so they can approve or reject it.
 */
class SparePartRequestedNotification extends OperationNotification
{
    public function __construct(public SparePartRequest $sparePartRequest) {}

    protected function type(): NotificationType
    {
        return NotificationType::WARNING;
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
        return __('Spare part change requested');
    }

    protected function body(object $notifiable): string
    {
        $this->sparePartRequest->loadMissing('material');

        return __('The technician requested to change the part ":part". Please review and approve or reject it.', [
            'part' => $this->sparePartRequest->material?->name,
        ]);
    }
}