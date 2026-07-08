<?php

namespace App\Services\Operations;

use App\Repositories\Eloquent\CarTypeRepository;

class CarTypeService
{
    public function __construct(protected CarTypeRepository $carTypeRepository)
    {

    }
    public function getAllCarTypes(): array
    {
        return $this->carTypeRepository->getAllCarTypes();
    }
}
