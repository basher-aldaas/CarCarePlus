<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\CarTypeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CarTypeRequest\CreateCarTypeRequest;
use App\Http\Requests\OperationsRequest\CarTypeRequest\UpdateCarTypeRequest;
use App\Http\Resources\CarTypeResource;
use App\Http\Responses\Response;
use App\Models\CarType;
use App\Services\Operations\CarTypeService;

class CarTypeController extends Controller
{
    public function __construct(
        protected CarTypeService $carTypeService
    )
    {
    }

    public function index()
    {
        return Response::Success(
            CarTypeResource::collection(
                $this->carTypeService->index()
            ),
            'Car types fetched successfully'
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new CarTypeResource(
                $this->carTypeService->show($id)
            ),
            'Car type fetched successfully'
        );
    }

    public function store(CreateCarTypeRequest $request)
    {
        $dto = CarTypeDTO::fromArray(
            $request->validated()
        );

        $carType = $this->carTypeService
            ->store($dto);

        return Response::Success(
            new CarTypeResource($carType),
            'Car type created successfully'
        );
    }

    public function update(UpdateCarTypeRequest $request, CarType $carType)
    {
        $dto = CarTypeDTO::fromArray(
            $request->validated()
        );

        $carType = $this->carTypeService
            ->update($carType, $dto);

        return Response::Success(
            new CarTypeResource($carType),
            'Car type updated successfully'
        );
    }

    public function destroy(CarType $carType)
    {
        $this->carTypeService
            ->destroy($carType);

        return Response::Success(
            [],
            'Car type deleted successfully'
        );
    }
}