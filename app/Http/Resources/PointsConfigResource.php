<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointsConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'earn_per_amount' => $this->earn_per_amount,//عدد النقاط المكتسبة لكل 1 دولار
            'redeem_value' => $this->redeem_value, //قيمة النقاط المساوية لكل 1 دولار
            'min_redeem' => $this->min_redeem,// الحد الأدنى للنقاط التي يمكن استردادها
            'expiry_days' => $this->expiry_days,//عدد الأيام قبل انتهاء صلاحية النقاط
            'max_earn_per_order' => $this->max_earn_per_order,// الحد الأقصى للنقاط التي يمكن كسبها لكل طلب
            'is_active' => (bool) $this->is_active,
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
