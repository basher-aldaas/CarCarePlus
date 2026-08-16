<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\DTOs\MaterialDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\MaterialRequest\CreateMaterialRequest;
use App\Http\Requests\OperationsRequest\MaterialRequest\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Http\Responses\Response;
use App\Models\Material;
use App\Services\Operations\MaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
{

    public function __construct(
        protected MaterialService $materialService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: MaterialResource::collection($this->materialService->index($request->integer('per_page', 15))),
            message: __('materials fetched successfully')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMaterialRequest $request): JsonResponse
    {
        $dto = MaterialDTO::fromArray(
            $request->validated()
        );

        $result = $this->materialService->store($dto);

        return Response::Success(
            data: new MaterialResource($result),
            message: __('material created successfully'),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material): JsonResponse
    {
        return Response::Success(
            new MaterialResource($this->materialService->show($material)),
            'material fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMaterialRequest $request, Material $material): JsonResponse
    {
        $dto = MaterialDTO::fromArray(
            $request->validated()
        );

        $result = $this->materialService->update($material, $dto);

        return Response::Success(
            data: new MaterialResource($result),
            message: __('material updated successfully'),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        $this->materialService->delete($material);

        return Response::Success(
            data: [],
            message: __('material deleted successfully')
        );
    }
}
