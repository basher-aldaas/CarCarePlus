<?php

namespace App\Repositories\Eloquent;

use App\Models\CarType;

class CarTypeRepository
{
    public function getAllCarTypes(): array
    {
        return CarType::all()->toArray();
    }
}
