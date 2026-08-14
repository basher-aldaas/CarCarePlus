<?php

namespace App\Http\Controllers\SuperAdmin\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseRequestItemResource;
use App\Http\Responses\Response;
use App\Models\PurchaseRequestItem;
use App\Services\Operations\PurchaseRequestItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseRequestItemController extends Controller
{
    public function __construct(
        protected PurchaseRequestItemService $purchaseRequestItemService
    )
    {}

    /**
     * Display a listing of the resource, optionally filtered by purchase request.
     */
    public function index(Request $request): JsonResponse
    {
        return Response::Success(
            data: PurchaseRequestItemResource::collection(
                $this->purchaseRequestItemService->index(
                    $request->integer('per_page', 15),
                    $request->has('purchase_req_id') ? $request->integer('purchase_req_id') : null
                )
            ),
            message: 'purchase request items fetched successfully'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseRequestItem $purchaseRequestItem): JsonResponse
    {
        return Response::Success(
            new PurchaseRequestItemResource($this->purchaseRequestItemService->show($purchaseRequestItem)),
            'purchase request item fetched successfully'
        );
    }
}