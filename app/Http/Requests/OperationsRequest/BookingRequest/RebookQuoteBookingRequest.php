<?php

namespace App\Http\Requests\OperationsRequest\BookingRequest;

/**
 * Validates the customer's edited rebooking form. It reuses every rule from
 * the normal create-booking flow (car ownership, branch/location, materials,
 * sub-services, payment, booking-type details) so a rebooking is validated
 * exactly like a first-time booking.
 *
 * The one difference: `service_id` is not accepted here. The main service is
 * locked to the original order and is forced server-side in
 * {@see \App\Services\Operations\BookingQuoteService::rebookQuote()}, so any
 * value the client sends for it is ignored.
 */
class RebookQuoteBookingRequest extends CreateBookingRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Service is locked to the original booking; the client cannot change it.
        unset($rules['service_id']);

        return $rules;
    }
}