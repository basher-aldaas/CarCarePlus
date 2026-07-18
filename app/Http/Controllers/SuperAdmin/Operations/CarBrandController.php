<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\CarBrandDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CarBrandRequest\CreateCarBrandRequest;
use App\Http\Requests\OperationsRequest\CarBrandRequest\UpdateCarBrandRequest;
use App\Http\Resources\CarBrandResource;
use App\Http\Responses\Response;
use App\Models\CarBrand;
use App\Services\Operations\CarBrandService;

class CarBrandController extends Controller
{
    public function __construct(
        protected CarBrandService $carBrandService
    )
    {
    }

    public function index()
    {
        return Response::Success(
            CarBrandResource::collection(
                $this->carBrandService->index()
            ),
            'Car brands fetched successfully'
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new CarBrandResource(
                $this->carBrandService->show($id)
            ),
            'Car brand fetched successfully'
        );
    }

    public function store(CreateCarBrandRequest $request)
    {
        $dto = CarBrandDTO::fromArray(
            $request->validated()
        );

        $carBrand = $this->carBrandService
            ->store($dto);

        return Response::Success(
            new CarBrandResource($carBrand),
            'Car brand created successfully'
        );
    }

    public function update(UpdateCarBrandRequest $request, CarBrand $carBrand)
    {
        $dto = CarBrandDTO::fromArray(
            $request->validated()
        );

        $carBrand = $this->carBrandService
            ->update($carBrand, $dto);

        return Response::Success(
            new CarBrandResource($carBrand),
            'Car brand updated successfully'
        );
    }

    public function destroy(CarBrand $carBrand)
    {
        $this->carBrandService
            ->destroy($carBrand);

        return Response::Success(
            [],
            'Car brand deleted successfully'
        );
    }
}