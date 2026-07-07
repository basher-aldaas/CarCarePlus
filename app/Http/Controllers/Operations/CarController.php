<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CarsRequest\AddCarRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function addCar(AddCarRequest $request): JsonResponse
    {
        $dto = AddCarDTO::fromArray($request->validated());
    }
}
