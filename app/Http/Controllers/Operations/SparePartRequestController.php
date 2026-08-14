<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\SparePartRequestDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\SparePartRequestRequest\CreateSparePartRequestRequest;
use App\Http\Requests\OperationsRequest\SparePartRequestRequest\DecideSparePartRequestRequest;
use App\Http\Resources\SparePartRequestResource;
use App\Http\Responses\Response;
use App\Models\SparePartRequest;
use App\Services\Operations\SparePartRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SparePartRequestController extends Controller
{
    public function __construct(
        protected SparePartRequestService $sparePartRequestService
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['order_id', 'employee_id', 'status']);

        return Response::Success(
            data: SparePartRequestResource::collection($this->sparePartRequestService->index($request->integer('per_page', 15), $filters)),
            message: 'spare part requests fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSparePartRequestRequest $request): JsonResponse
    {
        $employeeId = $request->user()->employee?->id;

        abort_unless($employeeId !== null, 403, __('Only employees can request spare parts'));

        $dto = SparePartRequestDTO::fromArray($request->validated());

        $result = $this->sparePartRequestService->store($dto, $employeeId);

        return Response::Success(
            data: new SparePartRequestResource($result),
            message: 'spare part request created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(SparePartRequest $sparePartRequest): JsonResponse
    {
        return Response::Success(
            new SparePartRequestResource($this->sparePartRequestService->show($sparePartRequest)),
            'spare part request fetched successfully'
        );
    }

    /**
     * Approve the specified spare part request.
     */
    public function approve(DecideSparePartRequestRequest $request, SparePartRequest $sparePartRequest): JsonResponse
    {
        $result = $this->sparePartRequestService->approve($sparePartRequest, $request->user()->id, $request->validated('notes'));

        return Response::Success(
            data: new SparePartRequestResource($result),
            message: 'spare part request approved successfully',
        );
    }

    /**
     * Reject the specified spare part request.
     */
    public function reject(DecideSparePartRequestRequest $request, SparePartRequest $sparePartRequest): JsonResponse
    {
        $result = $this->sparePartRequestService->reject($sparePartRequest, $request->user()->id, $request->validated('notes'));

        return Response::Success(
            data: new SparePartRequestResource($result),
            message: 'spare part request rejected successfully',
        );
    }
}