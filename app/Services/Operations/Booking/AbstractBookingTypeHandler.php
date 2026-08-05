<?php

namespace App\Services\Operations\Booking;

use App\Models\Order;

/**
 * Default no-op validate()/afterCreate() so a concrete handler only needs
 * to override what actually differs for its booking kind.
 */
abstract class AbstractBookingTypeHandler implements BookingTypeHandlerInterface
{
    abstract public function supports(array $data): bool;

    public function validate(array $data): void
    {
        //
    }

    public function afterCreate(Order $order, array $data): void
    {
        //
    }
}
