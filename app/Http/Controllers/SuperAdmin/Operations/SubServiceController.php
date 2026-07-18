<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\SubServiceDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\SubServiceRequest\CreateSubServiceRequest;
use App\Http\Requests\OperationsRequest\SubServiceRequest\UpdateSubServiceRequest;
use App\Http\Resources\SubServiceResource;
use App\Http\Responses\Response;
use App\Models\SubService;
use App\Services\Operations\SubServiceService;

class SubServiceController extends Controller
{
    public function __construct(
        protected SubServiceService $subServiceService
    )
    {
    }

    public function index()
    {
        return Response::Success(
            SubServiceResource::collection(
                $this->subServiceService->index()
            ),
            'Sub services fetched successfully'
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new SubServiceResource(
                $this->subServiceService->show($id)
            ),
            'Sub service fetched successfully'
        );
    }

    public function store(CreateSubServiceRequest $request)
    {
        $dto = SubServiceDTO::fromArray(
            $request->validated()
        );

        $subService = $this->subServiceService
            ->store($dto);

        return Response::Success(
            new SubServiceResource($subService),
            'Sub service created successfully'
        );
    }

    public function update(UpdateSubServiceRequest $request, SubService $subService)
    {
        $dto = SubServiceDTO::fromArray(
            $request->validated()
        );

        $subService = $this->subServiceService
            ->update($subService, $dto);

        return Response::Success(
            new SubServiceResource($subService),
            'Sub service updated successfully'
        );
    }

    public function destroy(SubService $subService)
    {
        $this->subServiceService
            ->destroy($subService);

        return Response::Success(
            [],
            'Sub service deleted successfully'
        );
    }
}