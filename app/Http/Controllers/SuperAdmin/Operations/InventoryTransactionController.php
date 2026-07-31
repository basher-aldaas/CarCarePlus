<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\InventoryTransactionRequest\CreateInventoryTransactionRequest;
use App\Http\Resources\InventoryTransactionResource;
use App\Http\Responses\Response;
use App\Models\InventoryTransaction;
use App\Services\Operations\InventoryTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryTransactionController extends Controller
{

    public function __construct(
        protected InventoryTransactionService $inventoryTransactionService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return Response::Success(
            data: InventoryTransactionResource::collection($this->inventoryTransactionService->index()),
            message: 'inventory transactions fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateInventoryTransactionRequest $request): JsonResponse
    {
        $result = $this->inventoryTransactionService->store(
            $request->validated(),
            $request->user()->id
        );

        return Response::Success(
            data: new InventoryTransactionResource($result),
            message: 'inventory transaction recorded successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryTransaction $inventoryTransaction): JsonResponse
    {
        return Response::Success(
            new InventoryTransactionResource($this->inventoryTransactionService->show($inventoryTransaction)),
            'inventory transaction fetched successfully'
        );
    }
}
