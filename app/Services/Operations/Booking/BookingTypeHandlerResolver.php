<?php

namespace App\Services\Operations\Booking;

use RuntimeException;

/**
 * Picks the right BookingTypeHandlerInterface for the submitted booking
 * data. Adding a new booking kind later is one new handler class plus one
 * line here — nothing else in the booking flow changes.
 */
class BookingTypeHandlerResolver
{
    /** @var BookingTypeHandlerInterface[] */
    protected array $handlers;

    public function __construct(
        RoadAssistanceBookingHandler $roadAssistanceBookingHandler,
        RegularServiceBookingHandler $regularServiceBookingHandler,
    ) {
        $this->handlers = [
            $roadAssistanceBookingHandler,
            $regularServiceBookingHandler,
        ];
    }

    public function resolve(array $data): BookingTypeHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($data)) {
                return $handler;
            }
        }

        throw new RuntimeException('No booking type handler supports the given booking data.');
    }
}
