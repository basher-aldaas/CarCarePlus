<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\PackageServiceSubServiceDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\PackageServiceSubServiceRequest\CreatePackageServiceSubServiceRequest;
use App\Http\Requests\OperationsRequest\PackageServiceSubServiceRequest\UpdatePackageServiceSubServiceRequest;
use App\Http\Resources\PackageServiceSubServiceResource;
use App\Http\Responses\Response;
use App\Models\PackageServiceSubService;
use App\Services\Operations\PackageServiceSubServiceService;
use Illuminate\Http\Request;

class PackageServiceSubServiceController extends Controller
{
    public function __construct(
        protected PackageServiceSubServiceService $packageServiceSubServiceService
    )
    {
    }

    public function index(Request $request)
    {
        return Response::Success(
            PackageServiceSubServiceResource::collection(
                $this->packageServiceSubServiceService->index($request->integer('per_page', 15))
            ),
            __('Package service sub services fetched successfully')
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new PackageServiceSubServiceResource(
                $this->packageServiceSubServiceService->show($id)
            ),
            __('Package service sub service fetched successfully')
        );
    }

    public function store(CreatePackageServiceSubServiceRequest $request)
    {
        $dto = PackageServiceSubServiceDTO::fromArray(
            $request->validated()
        );

        $packageServiceSubService = $this->packageServiceSubServiceService
            ->store($dto);

        return Response::Success(
            new PackageServiceSubServiceResource($packageServiceSubService),
            __('Package service sub service created successfully')
        );
    }

    public function update(UpdatePackageServiceSubServiceRequest $request, PackageServiceSubService $packageServiceSubService)
    {
        $dto = PackageServiceSubServiceDTO::fromArray(
            $request->validated()
        );

        $packageServiceSubService = $this->packageServiceSubServiceService
            ->update($packageServiceSubService, $dto);

        return Response::Success(
            new PackageServiceSubServiceResource($packageServiceSubService),
            __('Package service sub service updated successfully')
        );
    }

    public function destroy(PackageServiceSubService $packageServiceSubService)
    {
        $this->packageServiceSubServiceService
            ->destroy($packageServiceSubService);

        return Response::Success(
            [],
            __('Package service sub service deleted successfully')
        );
    }
}