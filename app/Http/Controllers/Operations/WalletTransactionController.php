<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletTransactionResource;
use App\Http\Responses\Response;
use App\Services\Operations\WalletTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    public function __construct(protected WalletTransactionService $walletTransactionService)
    {}

    /**
     * Wallet transactions. SA/admin may pass a customer_id to view any user's
     * history; other roles always see their own.
     */
    public function index(Request $request, ?int $customer_id = null): JsonResponse
    {
        return Response::Success(
            data: WalletTransactionResource::collection(
                $this->walletTransactionService->index($customer_id, $request->integer('per_page', 15))
            ),
            message: __('Wallet transactions retrieved successfully')
        );
    }

    public function show(int $id): JsonResponse
    {
        return Response::Success(
            data: new WalletTransactionResource($this->walletTransactionService->show($id)),
            message: __('Wallet transaction retrieved successfully')
        );
    }
}