<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Responses\Response;
use App\Services\Operations\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {}

    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentService->index(
            $request->integer('per_page', 15)
        );

        return Response::Success(
            data: PaymentResource::collection($payments),
            message: __('Payments retrieved successfully')
        );
    }

    public function show(int $id): JsonResponse
    {
        return Response::Success(
            data: new PaymentResource($this->paymentService->show($id)),
            message: __('Payment retrieved successfully')
        );
    }

    /**
     * Staff confirm a pending cash payment was collected.
     */
    public function confirmCash(int $id): JsonResponse
    {
        return Response::Success(
            data: new PaymentResource($this->paymentService->confirmCash($id)),
            message: __('Cash payment confirmed successfully')
        );
    }
}
