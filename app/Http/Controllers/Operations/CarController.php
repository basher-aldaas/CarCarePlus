<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\CarsRequest\AddCarRequest;
use Illuminate\Http\JsonResponse;

use App\DTOs\CarsDTOs\CreateCarDTO;
use App\DTOs\CarsDTOs\UpdateCarDTO;
use App\Http\Requests\OperationsRequest\CarsRequest\UpdateCarRequest;
use App\Http\Resources\CarResource;
use App\Http\Responses\Response;
use App\Services\Operations\CarService;



class CarController extends Controller
{

    protected $carService;

    public function __construct(
        CarService $carService
    ) {
        $this->carService = $carService;
    }

    /**
     * Show all authenticated user's vehicles.
     */
    public function index()
    {
        return Response::Success(

            CarResource::collection(
                $this->carService->index()
            ),

            'Vehicles retrieved successfully.'
        );
    }

    /**
     * Store vehicle.
     */
    public function store(AddCarRequest $request)
    {
        $result = $this->carService->store(

            CreateCarDTO::fromArray(
                $request->validated()
            )

        );

        return Response::Success(

            new CarResource($result['car']),

            'Vehicle created successfully.'

        );
    }

    /**
     * Update vehicle.
     */
    public function update(UpdateCarRequest $request, int $id)
    {


        $result = $this->carService->update(

            $id,

            UpdateCarDTO::fromArray(
                $request->validated()
            )
        );
        return Response::Success(

            new CarResource($result['car']),

            'Vehicle updated successfully.'
        );
    }

    /**
     * Delete vehicle.
     */
    public function destroy(int $id)
    {
        $this->carService->destroy($id);

        return Response::Success(

            null,

            'Vehicle deleted successfully.'

        );
    }
}
