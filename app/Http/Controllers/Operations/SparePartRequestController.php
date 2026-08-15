<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\SparePartRequestDTO;
use App\Exceptions\OnleEmployeeExciption;
use App\Exceptions\SparePartRequestShowUnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\SparePartRequestRequest\CreateSparePartRequestRequest;
use App\Http\Requests\OperationsRequest\SparePartRequestRequest\DecideSparePartRequestRequest;
use App\Http\Resources\SparePartRequestResource;
use App\Http\Responses\Response;
use App\Models\SparePartRequest;
use App\Services\Operations\SparePartRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function PHPUnit\Framework\throwException;

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
        return Response::Success(
            data: SparePartRequestResource::collection(
                $this->sparePartRequestService->index(
                    $request->integer('per_page', 15)
                )
            ),
            message: 'spare part requests fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSparePartRequestRequest $request): JsonResponse
    {
        $employeeId = $request->user()->employee?->id;


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
     * Approve the specified spare part request (order owner only).
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
     * Reject the specified spare part request (order owner only).
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
