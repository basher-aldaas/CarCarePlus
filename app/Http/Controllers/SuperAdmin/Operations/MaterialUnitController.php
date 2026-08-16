<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\MaterialUnitDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\MaterialUnitRequest\CreateMaterialUnitRequest;
use App\Http\Requests\OperationsRequest\MaterialUnitRequest\UpdateMaterialUnitRequest;
use App\Http\Resources\MaterialUnitResource;
use App\Http\Responses\Response;
use App\Models\MaterialUnit;
use App\Services\Operations\MaterialUnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialUnitController extends Controller
{

    public function __construct(
        protected MaterialUnitService $materialUnitService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: MaterialUnitResource::collection($this->materialUnitService->index($request->integer('per_page', 15))),
            message: __('material units fetched successfully')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMaterialUnitRequest $request): JsonResponse
    {
        $dto = MaterialUnitDTO::fromArray(
            $request->validated()
        );

        $result = $this->materialUnitService->store($dto);

        return Response::Success(
            data: new MaterialUnitResource($result),
            message: __('material unit created successfully'),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialUnit $materialUnit): JsonResponse
    {
        return Response::Success(
            new MaterialUnitResource($this->materialUnitService->show($materialUnit)),
            __('material unit fetched successfully')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMaterialUnitRequest $request, MaterialUnit $materialUnit): JsonResponse
    {
        $dto = MaterialUnitDTO::fromArray(
            $request->validated()
        );

        $result = $this->materialUnitService->update($materialUnit, $dto);

        return Response::Success(
            data: new MaterialUnitResource($result),
            message: __('material unit updated successfully'),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialUnit $materialUnit)
    {
        $this->materialUnitService->delete($materialUnit);

        return Response::Success(
            data: [],
            message: __('material unit deleted successfully')
        );
    }
}
