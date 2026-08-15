<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a towing-only action (e.g. submitting the delivery destination
 * coordinates) is attempted on a booking that has no towing detail — i.e. it
 * isn't a flatbed towing booking.
 */
class TowingDetailNotFoundException extends ApiException
{
    protected string $errorType = 'TOWING_DETAIL_NOT_FOUND';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_404_NOT_FOUND;

    public function getDefaultMessage(): string
    {
        return __('This booking has no towing detail.');
    }
}