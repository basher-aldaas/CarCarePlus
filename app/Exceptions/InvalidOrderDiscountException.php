<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a super admin applies an invalid discount to an order — a
 * non-positive amount, or one that exceeds the order's current total price.
 */
class InvalidOrderDiscountException extends ApiException
{
    protected string $errorType = 'INVALID_ORDER_DISCOUNT';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_422_UNPROCESSABLE_ENTITY;

    public function getDefaultMessage(): string
    {
        return __('The discount must be greater than zero and cannot exceed the order total.');
    }
}