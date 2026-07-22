<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\PointsConfigDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\PointsConfigRequest\CreatePointsConfigRequest;
use App\Http\Requests\OperationsRequest\PointsConfigRequest\UpdatePointsConfigRequest;
use App\Http\Resources\PointsConfigResource;
use App\Http\Responses\Response;
use App\Models\PointsConfig;
use App\Services\Operations\PointsConfigService;

class PointsConfigController extends Controller
{
    public function __construct(
        protected PointsConfigService $pointsConfigService
    )
    {
    }

    public function index()
    {
        return Response::Success(
            PointsConfigResource::collection(
                $this->pointsConfigService->index()
            ),
            'Points configs fetched successfully'
        );
    }

    public function show(int $id)
    {
        return Response::Success(
            new PointsConfigResource(
                $this->pointsConfigService->show($id)
            ),
            'Points config fetched successfully'
        );
    }

    public function store(CreatePointsConfigRequest $request)
    {
        $dto = PointsConfigDTO::fromArray(
            $request->validated()
        );

        $pointsConfig = $this->pointsConfigService
            ->store($dto);

        return Response::Success(
            new PointsConfigResource($pointsConfig),
            'Points config created successfully'
        );
    }

    public function update(UpdatePointsConfigRequest $request, PointsConfig $pointsConfig)
    {
        $dto = PointsConfigDTO::fromArray(
            $request->validated()
        );

        $pointsConfig = $this->pointsConfigService
            ->update($pointsConfig, $dto);

        return Response::Success(
            new PointsConfigResource($pointsConfig),
            'Points config updated successfully'
        );
    }

    public function destroy(PointsConfig $pointsConfig)
    {
        $this->pointsConfigService
            ->destroy($pointsConfig);

        return Response::Success(
            [],
            'Points config deleted successfully'
        );
    }
}