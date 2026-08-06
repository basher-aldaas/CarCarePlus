<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\PricingRuleDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\PricingRuleRequest\CreatePricingRuleRequest;
use App\Http\Requests\OperationsRequest\PricingRuleRequest\UpdatePricingRuleRequest;
use App\Http\Resources\PricingRuleResource;
use App\Http\Responses\Response;
use App\Models\PricingRule;
use App\Services\Operations\PricingRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingRuleController extends Controller
{

    public function __construct(
        protected PricingRuleService $pricingRuleService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: PricingRuleResource::collection($this->pricingRuleService->index($request->integer('per_page', 15))),
            message: 'pricing rules fetched successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePricingRuleRequest $request): JsonResponse
    {
        $dto = PricingRuleDTO::fromArray(
            $request->validated()
        );

        $result = $this->pricingRuleService->store($dto);

        return Response::Success(
            data: new PricingRuleResource($result),
            message: 'pricing rule created successfully',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingRule $pricingRule): JsonResponse
    {
        return Response::Success(
            new PricingRuleResource($this->pricingRuleService->show($pricingRule)),
            'pricing rule fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePricingRuleRequest $request, PricingRule $pricingRule): JsonResponse
    {
        $dto = PricingRuleDTO::fromArray(
            $request->validated()
        );

        $result = $this->pricingRuleService->update($pricingRule, $dto);

        return Response::Success(
            data: new PricingRuleResource($result),
            message: 'pricing rule updated successfully',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingRule $pricingRule)
    {
        $this->pricingRuleService->delete($pricingRule);

        return Response::Success(
            data: [],
            message: 'pricing rule deleted successfully'
        );
    }
}
