<?php

namespace App\Http\Controllers\Operations;

use App\Constants\HttpStatusConstants;
use App\DTOs\AdjustPointsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\PointRequest\AdjustPointsRequest;
use App\Http\Resources\PointsTransactionResource;
use App\Http\Responses\Response;
use App\Models\PointsTransaction;
use App\Services\Operations\PointService;
use Illuminate\Http\JsonResponse;

class PointsTransactionController extends Controller
{

    public function __construct(protected PointService $pointService)
    {}

    /**
     * List a customer's points transactions.
     * SA/admin may pass a customer_id to view any user's history; other roles always see their own.
     */
    public function index(?int $customer_id = null): JsonResponse
    {
        if (auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            if ($customer_id === null) {
                return Response::Error(data:null,message: 'customer_id is required');
            }
        } else {
            $customer_id = auth()->id();
        }

        $result = $this->pointService->getPointTransactionsById($customer_id);

        return Response::Success(
            data: PointsTransactionResource::collection($result),
            message: __('Point transactions retrieved successfully')
        );
    }

    /**
     * Show a single points transaction.
     * SA/admin may view any transaction; other roles may only view their own.
     */
    public function show(PointsTransaction $transaction): JsonResponse
    {
        if (!auth()->user()->hasAnyRole(['super_admin', 'admin']) && $transaction->customer_id !== auth()->id()) {
            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: HttpStatusConstants::HTTP_403_FORBIDDEN
            );
        }

        return Response::Success(
            data: new PointsTransactionResource($transaction),
            message: __('Point transaction retrieved successfully')
        );
    }

    /**
     * Add or deduct points for a customer, recording the transaction.
     */
    public function store(AdjustPointsRequest $request): JsonResponse
    {
        $dto = AdjustPointsDTO::fromArray($request->validated());

        $transaction = $this->pointService->adjustPoints($dto);

        return Response::Success(
            data: new PointsTransactionResource($transaction),
            message: __('Points adjusted successfully')
        );
    }
}