<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when an "out" or "transfer" inventory transaction would drive a
 * material's stock below zero for the branch.
 */
class InsufficientStockException extends ApiException
{
    protected string $errorType = 'INSUFFICIENT_STOCK';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_422_UNPROCESSABLE_ENTITY;

    public function getDefaultMessage(): string
    {
        return __('There is not enough stock available for this transaction.');
    }
}
