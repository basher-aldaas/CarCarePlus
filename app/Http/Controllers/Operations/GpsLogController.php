<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\GpsLogDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\GpsLogRequest\CreateGpsLogRequest;
use App\Http\Resources\GpsLogResource;
use App\Http\Responses\Response;
use App\Models\GpsLog;
use App\Services\Operations\GpsLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GpsLogController extends Controller
{
    public function __construct(
        protected GpsLogService $gpsLogService
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['employee_id', 'order_id']);

        return Response::Success(
            data: GpsLogResource::collection($this->gpsLogService->index($request->integer('per_page', 15), $filters)),
            message: __('gps logs fetched successfully')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateGpsLogRequest $request): JsonResponse
    {
        $employeeId = $request->user()->employee?->id;

        abort_unless($employeeId !== null, 403, __('Only employees can record gps logs'));

        $dto = GpsLogDTO::fromArray($request->validated());

        $result = $this->gpsLogService->store($dto, $employeeId);

        return Response::Success(
            data: new GpsLogResource($result),
            message: __('gps log recorded successfully'),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(GpsLog $gpsLog): JsonResponse
    {
        return Response::Success(
            new GpsLogResource($this->gpsLogService->show($gpsLog)),
            'gps log fetched successfully'
        );
    }
}