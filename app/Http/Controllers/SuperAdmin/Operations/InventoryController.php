<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\InventoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\InventoryRequest\CreateInventoryRequest;
use App\Http\Requests\OperationsRequest\InventoryRequest\UpdateInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Http\Responses\Response;
use App\Models\Inventory;
use App\Services\Operations\InventoryService;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{

    public function __construct(
        protected InventoryService $inventoryService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return Response::Success(
            data: InventoryResource::collection($this->inventoryService->index()),
            message: 'inventories fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateInventoryRequest $request): JsonResponse
    {
        $dto = InventoryDTO::fromArray(
            $request->validated()
        );

        $result = $this->inventoryService->store($dto);

        return Response::Success(
            data: new InventoryResource($result),
            message: 'inventory created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory): JsonResponse
    {
        return Response::Success(
            new InventoryResource($this->inventoryService->show($inventory)),
            'inventory fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryRequest $request, Inventory $inventory): JsonResponse
    {
        $dto = InventoryDTO::fromArray(
            $request->validated()
        );

        $result = $this->inventoryService->update($inventory, $dto);

        return Response::Success(
            data: new InventoryResource($result),
            message: 'inventory updated successfully',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        $this->inventoryService->delete($inventory);

        return Response::Success(
            data: [],
            message: 'inventory deleted successfully'
        );
    }
}
