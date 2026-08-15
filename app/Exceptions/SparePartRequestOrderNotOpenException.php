<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a spare part request is raised against an order that is no longer
 * open — a completed or cancelled order can no longer receive new parts.
 */
class SparePartRequestOrderNotOpenException extends ApiException
{
    protected string $errorType = 'SPARE_PART_REQUEST_ORDER_NOT_OPEN';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_422_UNPROCESSABLE_ENTITY;

    public function getDefaultMessage(): string
    {
        return __('You cannot request a spare part for a completed or cancelled order.');
    }
}