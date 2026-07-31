<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\SystemSettingDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\SystemSettingRequest\CreateSystemSettingRequest;
use App\Http\Requests\OperationsRequest\SystemSettingRequest\UpdateSystemSettingRequest;
use App\Http\Resources\SystemSettingResource;
use App\Http\Responses\Response;
use App\Models\SystemSetting;
use App\Services\Operations\SystemSettingService;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends Controller
{

    public function __construct(
        protected SystemSettingService $systemSettingService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return Response::Success(
            data: SystemSettingResource::collection($this->systemSettingService->index()),
            message: 'system settings fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSystemSettingRequest $request): JsonResponse
    {
        $dto = SystemSettingDTO::fromArray(
            $request->validated()
        );

        $result = $this->systemSettingService->store($dto);

        return Response::Success(
            data: new SystemSettingResource($result),
            message: 'system setting created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(SystemSetting $systemSetting): JsonResponse
    {
        return Response::Success(
            new SystemSettingResource($this->systemSettingService->show($systemSetting)),
            'system setting fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSystemSettingRequest $request, SystemSetting $systemSetting): JsonResponse
    {
        $dto = SystemSettingDTO::fromArray(
            $request->validated()
        );

        $result = $this->systemSettingService->update($systemSetting, $dto);

        return Response::Success(
            data: new SystemSettingResource($result),
            message: 'system setting updated successfully',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SystemSetting $systemSetting)
    {
        $this->systemSettingService->delete($systemSetting);

        return Response::Success(
            data: [],
            message: 'system setting deleted successfully'
        );
    }
}
