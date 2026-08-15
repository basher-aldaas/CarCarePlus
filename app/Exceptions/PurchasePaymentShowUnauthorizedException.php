<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to view a purchase payment that does not belong to
 * the branch he manages. Only a super admin, or the admin of the owning branch,
 * may view it.
 */
class PurchasePaymentShowUnauthorizedException extends ApiException
{
    protected string $errorType = 'PURCHASE_PAYMENT_SHOW_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not authorized to view this purchase payment.');
    }
}