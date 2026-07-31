<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\SuggestedProblemDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\SuggestedProblemRequest\CreateSuggestedProblemRequest;
use App\Http\Requests\OperationsRequest\SuggestedProblemRequest\UpdateSuggestedProblemRequest;
use App\Http\Resources\SuggestedProblemResource;
use App\Http\Responses\Response;
use App\Models\SuggestedProblem;
use App\Services\Operations\SuggestedProblemService;
use Illuminate\Http\JsonResponse;

class SuggestedProblemController extends Controller
{

    public function __construct(
        protected SuggestedProblemService $suggestedProblemService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return Response::Success(
            data: SuggestedProblemResource::collection($this->suggestedProblemService->index()),
            message: 'suggested problems fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSuggestedProblemRequest $request): JsonResponse
    {
        $dto = SuggestedProblemDTO::fromArray(
            $request->validated()
        );

        $result = $this->suggestedProblemService->store($dto);

        return Response::Success(
            data: new SuggestedProblemResource($result),
            message: 'suggested problem created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(SuggestedProblem $suggestedProblem): JsonResponse
    {
        return Response::Success(
            new SuggestedProblemResource($this->suggestedProblemService->show($suggestedProblem)),
            'suggested problem fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSuggestedProblemRequest $request, SuggestedProblem $suggestedProblem): JsonResponse
    {
        $dto = SuggestedProblemDTO::fromArray(
            $request->validated()
        );

        $result = $this->suggestedProblemService->update($suggestedProblem, $dto);

        return Response::Success(
            data: new SuggestedProblemResource($result),
            message: 'suggested problem updated successfully',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuggestedProblem $suggestedProblem)
    {
        $this->suggestedProblemService->delete($suggestedProblem);

        return Response::Success(
            data: [],
            message: 'suggested problem deleted successfully'
        );
    }
}
