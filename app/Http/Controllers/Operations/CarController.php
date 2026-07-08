<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\CarDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CarsRequest\CreateCarRequest;
use App\Http\Requests\OperationsRequest\CarsRequest\UpdateCarRequest;
use App\Http\Resources\CarResource;
use App\Http\Responses\Response;
use App\Services\Operations\CarService;
use Illuminate\Http\JsonResponse;

class CarController extends Controller
{
    public function __construct(public CarService $carService)
    {}

    //Get all Cars in system for admins and super admin
    public function indexDashboard(): JsonResponse
    {
        $result = $this->carService->getAllDashboardCars();

        return Response::Success(
            data: CarResource::collection($result),
            message: __('Cars retrieved successfully')
        );
    }

    //Get all Cars in system for clients
    public function indexClient(): JsonResponse
    {
        $result = $this->carService->getAllClientCars(auth()->id());

        return Response::Success(
            data: CarResource::collection($result),
            message: __('User cars retrieved successfully')
        );
    }
    public function store(CreateCarRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['customer_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('cars', 'public');
        }

        $dto = CarDTO::fromArray($data);
        $result = $this->carService->createCar($dto);

        return Response::Success(
            data: new CarResource($result),
            message: __('Car added successfully')
        );
    }

    public function update(UpdateCarRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('cars', 'public');
        }

        $dto = CarDTO::fromArray($data);
        $result = $this->carService->updateCar($dto, $id);

        return Response::Success(
            data: new CarResource($result),
            message: __('Car updated successfully')
        );
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->carService->getCarById($id);

        return Response::Success(
            data: new CarResource($result),
            message: __('Car retrieved successfully')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->carService->deleteCar($id);

        return Response::Success(
            data: [] ,
            message: __('Car deleted successfully')
        );
    }
}
