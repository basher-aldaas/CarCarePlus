<?php


namespace App\Http\Controllers\Operations;

use App\DTOs\ServiceDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\ServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Responses\Response;
use App\Models\Service;
use App\Services\Operations\ServiceService;

class ServiceController extends Controller
{
    public function __construct(
        protected ServiceService $serviceService
    )
    {
    }

    public function index()
    {
        return Response::Success(
            ServiceResource::collection(
                $this->serviceService->index()
            ),
            'Services fetched successfully'
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new ServiceResource(
                $this->serviceService->show($id)
            ),
            'Service fetched successfully'
        );
    }

    public function store(ServiceRequest $request)
    {
        $dto = ServiceDTO::fromArray(
            $request->validated()
        );

        $service = $this->serviceService
            ->store($dto);

        return Response::Success(
            new ServiceResource($service),
            'Service created successfully'
        );
    }

    public function update(
        ServiceRequest $request,
        Service        $service
    )
    {
        $dto = ServiceDTO::fromArray(
            $request->validated()
        );

        $service = $this->serviceService
            ->update($service, $dto);

        return Response::Success(
            new ServiceResource($service),
            'Service updated successfully'
        );
    }

    public function destroy(Service $service)
    {
        $this->serviceService
            ->destroy($service);

        return Response::Success(
            [],
            'Service deleted successfully'
        );
    }
}
