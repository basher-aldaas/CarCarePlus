<?php

namespace App\Services\Operations;

use App\DTOs\ApplyOrderServiceDTO;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class OrderServiceApplicationService
{
    public function apply(ApplyOrderServiceDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {

            /*
            |--------------------------------------------------------------------------
            | البحث عن الطلب
            |--------------------------------------------------------------------------
            */

            $order = Order::findOrFail($dto->order_id);

            /*
            |--------------------------------------------------------------------------
            | البحث عن الخدمة
            |--------------------------------------------------------------------------
            */

            $service = Service::query()
                ->with('category')
                ->findOrFail($dto->service_id);

            /*
            |--------------------------------------------------------------------------
            | تطبيق الخدمة على الطلب
            |--------------------------------------------------------------------------
            */

            $order->update([
                'service_id' => $service->id,
                'category_id' => $service->category_id,
                'total_price' => $service->base_price,
            ]);

            /*
            |--------------------------------------------------------------------------
            | إعادة الطلب مع العلاقات
            |--------------------------------------------------------------------------
            */

            return $order
                ->refresh()
                ->load([
                    'customer',
                    'car',
                    'branch',
                    'employee.user',
                    'service',
                    'category',
                    'priceItems',
                ]);
        });
    }
}
