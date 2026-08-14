<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\ContractDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\ContractRequest\CreateContractRequest;
use App\Http\Requests\OperationsRequest\ContractRequest\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Http\Responses\Response;
use App\Models\Contract;
use App\Services\Operations\ContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct(
        protected ContractService $contractService
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: ContractResource::collection($this->contractService->index($request->integer('per_page', 15))),
            message: 'contracts fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateContractRequest $request): JsonResponse
    {
        $dto = ContractDTO::fromArray($request->validated());

        $result = $this->contractService->store($dto, $request->user()->id);

        return Response::Success(
            data: new ContractResource($result),
            message: 'contract created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract): JsonResponse
    {
        return Response::Success(
            new ContractResource($this->contractService->show($contract)),
            'contract fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractRequest $request, Contract $contract): JsonResponse
    {
        $dto = ContractDTO::fromArray($request->validated());

        $result = $this->contractService->update($contract, $dto);

        return Response::Success(
            data: new ContractResource($result),
            message: 'contract updated successfully',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract): JsonResponse
    {
        $this->contractService->delete($contract);

        return Response::Success(
            data: [],
            message: 'contract deleted successfully'
        );
    }
}