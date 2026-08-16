<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\ProblemTypeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\ProblemTypeRequest\CreateProblemTypeRequest;
use App\Http\Requests\OperationsRequest\ProblemTypeRequest\UpdateProblemTypeRequest;
use App\Http\Resources\ProblemTypeResource;
use App\Http\Responses\Response;
use App\Models\ProblemType;
use App\Services\Operations\ProblemTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProblemTypeController extends Controller
{

    public function __construct(
        protected ProblemTypeService $problemTypeService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: ProblemTypeResource::collection($this->problemTypeService->index($request->integer('per_page', 15))),
            message: __('problem types fetched successfully')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProblemTypeRequest $request): JsonResponse
    {
        $dto = ProblemTypeDTO::fromArray(
            $request->validated()
        );

        $result = $this->problemTypeService->store($dto);

        return Response::Success(
            data: new ProblemTypeResource($result),
            message: __('problem type created successfully'),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(ProblemType $problemType): JsonResponse
    {
        return Response::Success(
            new ProblemTypeResource($this->problemTypeService->show($problemType)),
            __('problem type fetched successfully')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProblemTypeRequest $request, ProblemType $problemType): JsonResponse
    {
        $dto = ProblemTypeDTO::fromArray(
            $request->validated()
        );

        $result = $this->problemTypeService->update($problemType, $dto);

        return Response::Success(
            data: new ProblemTypeResource($result),
            message: __('problem type updated successfully'),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProblemType $problemType)
    {
        $this->problemTypeService->delete($problemType);

        return Response::Success(
            data: [],
            message: __('problem type deleted successfully')
        );
    }
}
