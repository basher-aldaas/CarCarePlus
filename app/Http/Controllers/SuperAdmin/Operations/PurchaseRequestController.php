<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\PurchaseRequestDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\PurchaseRequestRequest\CreatePurchaseRequestRequest;
use App\Http\Requests\OperationsRequest\PurchaseRequestRequest\RejectPurchaseRequestRequest;
use App\Http\Requests\OperationsRequest\PurchaseRequestRequest\TransferPurchaseRequestRequest;
use App\Http\Requests\OperationsRequest\PurchaseRequestRequest\UpdatePurchaseRequestRequest;
use App\Http\Resources\PurchaseRequestResource;
use App\Http\Responses\Response;
use App\Models\PurchaseRequest;
use App\Services\Operations\PurchaseRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function __construct(
        protected PurchaseRequestService $purchaseRequestService
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: PurchaseRequestResource::collection($this->purchaseRequestService->index($request->integer('per_page', 15))),
            message: 'purchase requests fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePurchaseRequestRequest $request): JsonResponse
    {
        $dto = PurchaseRequestDTO::fromArray($request->validated());

        $result = $this->purchaseRequestService->store($dto, $request->validated('items'));

        return Response::Success(
            data: new PurchaseRequestResource($result),
            message: 'purchase request created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        return Response::Success(
            new PurchaseRequestResource($this->purchaseRequestService->show($purchaseRequest)),
            'purchase request fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        $dto = PurchaseRequestDTO::fromArray($request->validated());

        $result = $this->purchaseRequestService->update($purchaseRequest, $dto, $request->validated('items'));

        return Response::Success(
            data: new PurchaseRequestResource($result),
            message: 'purchase request updated successfully',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->purchaseRequestService->delete($purchaseRequest);

        return Response::Success(
            data: [],
            message: 'purchase request deleted successfully'
        );
    }

    /**
     * Transfer materials directly between two branches (created and approved
     * immediately by the super admin).
     */
    public function transfer(TransferPurchaseRequestRequest $request): JsonResponse
    {
        $result = $this->purchaseRequestService->storeTransfer($request->validated());

        return Response::Success(
            data: new PurchaseRequestResource($result),
            message: 'materials transferred successfully',
        );
    }

    /**
     * Approve the specified purchase request.
     */
    public function approve(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $result = $this->purchaseRequestService->approve($purchaseRequest);

        return Response::Success(
            data: new PurchaseRequestResource($result),
            message: 'purchase request approved successfully',
        );
    }

    /**
     * Reject the specified purchase request.
     */
    public function reject(RejectPurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        $result = $this->purchaseRequestService->reject($purchaseRequest, $request->validated('rejection_reason'));

        return Response::Success(
            data: new PurchaseRequestResource($result),
            message: 'purchase request rejected successfully',
        );
    }
}
