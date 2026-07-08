<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarTypeResource;
use App\Http\Responses\Response;
use App\Services\Operations\CarTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarTypeController extends Controller
{

    public function __construct(
       protected CarTypeService $carTypeService,
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $result = $this->carTypeService->getAllCarTypes();

        return Response::Success(
            data: new CarTypeResource($result),
            message: __('Car types retrieved successfully')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
