<?php

namespace App\Http\Controllers\Operations;

use App\Constants\HttpStatusConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsRequest\WalletRequest\AdjustWalletRequest;
use App\Http\Resources\WalletResource;
use App\Http\Responses\Response;
use App\Services\Operations\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {}

    /**
     * All wallets (super admin / admin overview).
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: WalletResource::collection($this->walletService->index($request->integer('per_page', 15))),
            message: __('Wallets retrieved successfully')
        );
    }

    /**
     * The authenticated user's own wallet.
     */
    public function myWallet(): JsonResponse
    {
        return Response::Success(
            data: new WalletResource($this->walletService->forUser(auth()->id())),
            message: __('Wallet retrieved successfully')
        );
    }

    /**
     * A specific user's wallet. SA/admin may target anyone; other roles are
     * limited to their own.
     */
    public function show(int $customer_id): JsonResponse
    {
        $user = auth()->user();

        if (! $user->hasAnyRole(['super_admin', 'admin']) && $customer_id !== $user->id) {
            return Response::Error(
                data: null,
                message: __('Unauthorized'),
                code: HttpStatusConstants::HTTP_403_FORBIDDEN
            );
        }

        return Response::Success(
            data: new WalletResource($this->walletService->forUser($customer_id)),
            message: __('Wallet retrieved successfully')
        );
    }

    /**
     * Adjust a user's wallet balance (staff). Positive credits, negative debits.
     */
    public function adjust(AdjustWalletRequest $request, int $customer_id): JsonResponse
    {
        $wallet = $this->walletService->adjust(
            userId: $customer_id,
            amount: (float) $request->validated('amount'),
            note: $request->validated('note'),
        );

        return Response::Success(
            data: new WalletResource($wallet),
            message: __('Wallet adjusted successfully')
        );
    }
}