<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\PackageDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\PackageRequest\CreatePackageRequest;
use App\Http\Requests\OperationsRequest\PackageRequest\UpdatePackageRequest;
use App\Http\Resources\PackageResource;
use App\Http\Responses\Response;
use App\Models\Package;
use App\Services\Operations\PackageService;

class PackageController extends Controller
{
    public function __construct(
        protected PackageService $packageService
    )
    {
    }

    public function index()
    {
        return Response::Success(
            PackageResource::collection(
                $this->packageService->index()
            ),
            'Packages fetched successfully'
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new PackageResource(
                $this->packageService->show($id)
            ),
            'Package fetched successfully'
        );
    }

    public function store(CreatePackageRequest $request)
    {
        $dto = PackageDTO::fromArray(
            $request->validated()
        );

        $package = $this->packageService
            ->store($dto);

        return Response::Success(
            new PackageResource($package),
            'Package created successfully'
        );
    }

    public function update(UpdatePackageRequest $request, Package $package)
    {
        $dto = PackageDTO::fromArray(
            $request->validated()
        );

        $package = $this->packageService
            ->update($package, $dto);

        return Response::Success(
            new PackageResource($package),
            'Package updated successfully'
        );
    }

    public function destroy(Package $package)
    {
        $this->packageService
            ->destroy($package);

        return Response::Success(
            [],
            'Package deleted successfully'
        );
    }
}