<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\PricingRuleTypeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\PricingRuleTypeRequest\CreatePricingRuleTypeRequest;
use App\Http\Requests\OperationsRequest\PricingRuleTypeRequest\UpdatePricingRuleTypeRequest;
use App\Http\Resources\PricingRuleTypeResource;
use App\Http\Responses\Response;
use App\Models\PricingRuleType;
use App\Services\Operations\PricingRuleTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingRuleTypeController extends Controller
{

    public function __construct(
        protected PricingRuleTypeService $pricingRuleTypeService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: PricingRuleTypeResource::collection($this->pricingRuleTypeService->index($request->integer('per_page', 15))),
            message: __('pricing rule types fetched successfully')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePricingRuleTypeRequest $request): JsonResponse
    {
        $dto = PricingRuleTypeDTO::fromArray(
            $request->validated()
        );

        $result = $this->pricingRuleTypeService->store($dto);

        return Response::Success(
            data: new PricingRuleTypeResource($result),
            message: __('pricing rule type created successfully'),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingRuleType $pricingRuleType): JsonResponse
    {
        return Response::Success(
            new PricingRuleTypeResource($this->pricingRuleTypeService->show($pricingRuleType)),
            'pricing rule types fetched successfully '
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePricingRuleTypeRequest $request, PricingRuleType $pricingRuleType): JsonResponse
    {
        $dto = PricingRuleTypeDTO::fromArray(
            $request->validated()
        );

        $result = $this->pricingRuleTypeService->update($pricingRuleType, $dto);

        return Response::Success(
            data: new PricingRuleTypeResource($result),
            message: __('pricing rule type updated successfully'),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingRuleType $pricingRuleType)
    {
        $this->pricingRuleTypeService->delete($pricingRuleType);

        return Response::Success(
            data: [],
            message: __('pricing rule type deleted successfully')
        );
    }
}
