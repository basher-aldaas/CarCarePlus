<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\PointResource;
use App\Http\Responses\Response;
use App\Services\Operations\PointService;
use Illuminate\Http\JsonResponse;

class PointController extends Controller
{

    public function __construct(public PointService $pointService)
    {}

    /**
     * List all customers' points balances.
     */
    public function index(): JsonResponse
    {
        return Response::Success(
            data: PointResource::collection($this->pointService->index()),
            message: __('User points retrieved successfully')
        );
    }

    /**
     * Show a customer's points balance.
     * SA/admin may pass a customer_id to view any user's balance; other roles always see their own.
     */
    public function show(?int $customer_id = null): JsonResponse
    {
        if (auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            if ($customer_id === null) {
                return Response::Error(data:null,message: 'customer_id is required');
            }
        } else {
            $customer_id = auth()->id();
        }

        $result = $this->pointService->getPointById($customer_id);

        return Response::Success(
            data:new PointResource($result),
            message:__('Point retrieved successfully')
        );
    }
}