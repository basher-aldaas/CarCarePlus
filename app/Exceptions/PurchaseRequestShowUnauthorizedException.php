<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to view a purchase request (or one of its items)
 * that does not belong to the branch he manages. Only a super admin, or the
 * admin of the owning branch, may view it.
 */
class PurchaseRequestShowUnauthorizedException extends ApiException
{
    protected string $errorType = 'PURCHASE_REQUEST_SHOW_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not authorized to view this purchase request.');
    }
}