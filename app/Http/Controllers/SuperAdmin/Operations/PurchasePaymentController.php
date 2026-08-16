<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchasePaymentResource;
use App\Http\Responses\Response;
use App\Models\PurchasePayment;
use App\Services\Operations\PurchasePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchasePaymentController extends Controller
{
    public function __construct(
        protected PurchasePaymentService $purchasePaymentService
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: PurchasePaymentResource::collection($this->purchasePaymentService->index($request->integer('per_page', 15))),
            message: __('purchase payments fetched successfully')
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchasePayment $purchasePayment): JsonResponse
    {
        return Response::Success(
            data: new PurchasePaymentResource($this->purchasePaymentService->show($purchasePayment)),
            message: __('purchase payment fetched successfully')
        );
    }
}