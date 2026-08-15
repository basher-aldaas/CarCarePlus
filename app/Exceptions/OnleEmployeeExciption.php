<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;
use Exception;

class OnleEmployeeExciption extends ApiException
{
    protected string $errorType = 'ONLY_EMPLOYEE';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('Only employees can request spare parts');
    }
}
