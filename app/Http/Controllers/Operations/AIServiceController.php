<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\ApplyOrderServiceDTO;
use App\Http\Controllers\Controller;
use App\Services\Operations\OrderServiceApplicationService;
use Illuminate\Http\Request;

class AIServiceController extends Controller
{
    public function __construct(
        protected OrderServiceApplicationService $orderServiceApplicationService
    ) {
    }

    public function apply(Request $request)
    {
        $validated = $request->validate([
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],

            'service_id' => [
                'required',
                'integer',
                'exists:services,id',
            ],
        ]);

        $dto = ApplyOrderServiceDTO::fromArray($validated);

        $order = $this->orderServiceApplicationService->apply($dto);

        return response()->json([
            'success' => true,

            'message' => 'تم تطبيق الخدمة على طلب الصيانة بنجاح.',

            'data' => [
                'order_id' => $order->id,

                'service' => $order->service
                    ? [
                        'id' => $order->service->id,
                        'name' => $order->service->name,
                        'name_ar' => $order->service->name_ar,
                        'price' => $order->service->base_price,
                        'duration_minutes' => $order->service->duration_minutes,
                    ]
                    : null,

                'category_id' => $order->category_id,

                'total_price' => $order->total_price,
            ],
        ]);
    }
}
