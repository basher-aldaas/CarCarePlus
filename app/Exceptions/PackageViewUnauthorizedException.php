<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a customer tries to view a package that isn't targeted at
 * their customer type (personal vs. company).
 */
class PackageViewUnauthorizedException extends ApiException
{
    protected string $errorType = 'PACKAGE_VIEW_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('This package is not available for your account type.');
    }
}
