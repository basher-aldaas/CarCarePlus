<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\PackageServiceDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\PackageServiceRequest\CreatePackageServiceRequest;
use App\Http\Requests\OperationsRequest\PackageServiceRequest\UpdatePackageServiceRequest;
use App\Http\Resources\PackageServiceResource;
use App\Http\Responses\Response;
use App\Models\PackageService as PackageServiceModel;
use App\Services\Operations\PackageServiceService;

class PackageServiceController extends Controller
{
    public function __construct(
        protected PackageServiceService $packageServiceService
    )
    {
    }

    public function index()
    {
        return Response::Success(
            PackageServiceResource::collection(
                $this->packageServiceService->index()
            ),
            'Package services fetched successfully'
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new PackageServiceResource(
                $this->packageServiceService->show($id)
            ),
            'Package service fetched successfully'
        );
    }

    public function store(CreatePackageServiceRequest $request)
    {
        $dto = PackageServiceDTO::fromArray(
            $request->validated()
        );

        $packageService = $this->packageServiceService
            ->store($dto);

        return Response::Success(
            new PackageServiceResource($packageService),
            'Package service created successfully'
        );
    }

    public function update(UpdatePackageServiceRequest $request, PackageServiceModel $packageService)
    {
        $dto = PackageServiceDTO::fromArray(
            $request->validated()
        );

        $packageService = $this->packageServiceService
            ->update($packageService, $dto);

        return Response::Success(
            new PackageServiceResource($packageService),
            'Package service updated successfully'
        );
    }

    public function destroy(PackageServiceModel $packageService)
    {
        $this->packageServiceService
            ->destroy($packageService);

        return Response::Success(
            [],
            'Package service deleted successfully'
        );
    }
}