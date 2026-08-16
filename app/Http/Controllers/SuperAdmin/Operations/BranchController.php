<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\BranchesDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\BranchRequest\CreateBranchRequest;
use App\Http\Requests\OperationsRequest\BranchRequest\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Http\Responses\Response;
use App\Models\Branch;
use App\Services\Operations\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{

    public function __construct(
        protected BranchService $branchService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: BranchResource::collection($this->branchService->index($request->integer('per_page', 15))),
            message: __('branches fetched successfully')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBranchRequest $request): JsonResponse
    {
        $dto = BranchesDTO::fromArray(
            $request->validated()
        );

        $result = $this->branchService->store($dto);

        return Response::Success(
            data: new BranchResource($result),
            message: __('branch created successfully'),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch): JsonResponse
    {
        return Response::Success(
            new BranchResource($this->branchService->show($branch)),
            'branch fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): JsonResponse
    {
        $dto = BranchesDTO::fromArray(
            $request->validated()
        );

        $result = $this->branchService->update($branch, $dto);

        return Response::Success(
            data: new BranchResource($result),
            message: __('branch updated successfully'),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        $this->branchService->delete($branch);

        return Response::Success(
            data: [],
            message: __('branch deleted successfully')
        );
    }
}
