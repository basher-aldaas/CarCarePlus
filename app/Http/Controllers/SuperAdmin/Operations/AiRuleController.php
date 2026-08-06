<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\AiRuleDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\AiRuleRequest\CreateAiRuleRequest;
use App\Http\Requests\OperationsRequest\AiRuleRequest\UpdateAiRuleRequest;
use App\Http\Resources\AiRuleResource;
use App\Http\Responses\Response;
use App\Models\AiRule;
use App\Services\Operations\AiRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiRuleController extends Controller
{

    public function __construct(
        protected AiRuleService $aiRuleService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: AiRuleResource::collection($this->aiRuleService->index($request->integer('per_page', 15))),
            message: 'ai rules fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateAiRuleRequest $request): JsonResponse
    {
        $dto = AiRuleDTO::fromArray(
            $request->validated()
        );

        $result = $this->aiRuleService->store($dto);

        return Response::Success(
            data: new AiRuleResource($result),
            message: 'ai rule created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(AiRule $aiRule): JsonResponse
    {
        return Response::Success(
            new AiRuleResource($this->aiRuleService->show($aiRule)),
            'ai rule fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAiRuleRequest $request, AiRule $aiRule): JsonResponse
    {
        $dto = AiRuleDTO::fromArray(
            $request->validated()
        );

        $result = $this->aiRuleService->update($aiRule, $dto);

        return Response::Success(
            data: new AiRuleResource($result),
            message: 'ai rule updated successfully',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AiRule $aiRule)
    {
        $this->aiRuleService->delete($aiRule);

        return Response::Success(
            data: [],
            message: 'ai rule deleted successfully'
        );
    }
}
