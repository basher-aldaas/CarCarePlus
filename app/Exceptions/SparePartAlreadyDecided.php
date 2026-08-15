<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;
use Exception;

class SparePartAlreadyDecided extends ApiException
{
    protected string $errorType = 'SPARE_PART_REQUEST_ORDER_ALREADY_DECIDED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_422_UNPROCESSABLE_ENTITY;

    public function getDefaultMessage(): string
    {
        return __('This spare part request has already been decided.');
    }
}
