<?php

namespace App\Services\Operations\Booking;

class RegularServiceBookingHandler extends AbstractBookingTypeHandler
{
    public function supports(array $data): bool
    {
        return ! isset($data['problem_type_id']);
    }
}
