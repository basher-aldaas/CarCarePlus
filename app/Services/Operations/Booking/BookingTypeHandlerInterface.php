<?php

namespace App\Services\Operations\Booking;

use App\Models\Order;

interface BookingTypeHandlerInterface
{
    /**
     * Whether this handler is the right one for the submitted booking data.
     */
    public function supports(array $data): bool;

    /**
     * Validate the kind-specific fields. Throws ValidationException on
     * failure.
     */
    public function validate(array $data): void;

    /**
     * Persist any kind-specific satellite data once the order exists.
     */
    public function afterCreate(Order $order, array $data): void;
}
