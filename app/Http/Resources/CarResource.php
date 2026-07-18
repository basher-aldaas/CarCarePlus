<?php

namespace App\Http\Resources;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
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

            // المفاتيح الأجنبية (Foreign Keys)
            'user_id' => $this->user_id,
            'brand_id' => $this->brand_id,
            'car_type_id' => $this->car_type_id,
            'branch_id' => $this->branch_id,

            // البيانات الأساسية للسيارة
            'plate_number' => $this->plate_number,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,

            // enum مُدعّم — نُعيد القيمة النصية بشكل صريح
            'fuel_type' => $this->fuel_type?->value,
            'cylinders' => $this->cylinders,
            'mileage' => $this->mileage,

            // معالجة رابط الصورة ليعود كرابط كامل (Full URL) وليس فقط مسار التخزين
            'image_url' => $this->image_url ? asset('storage/' . $this->image_url) : null,

            // التأكد من أن القيمة تعود كـ Boolean صريح
            'is_active' => (bool) $this->is_active,

            // العلاقات — تظهر فقط عند تحميلها مسبقاً (eager loaded)
            'owner' => new UserResource($this->whenLoaded('owner')),
            'car_type' => new CarTypeResource($this->whenLoaded('carType')),
            'branch' => $this->whenLoaded('branch', fn () => [
                'id'      => $this->branch->id,
                'name'    => $this->branch->name,
                'name_ar' => $this->branch->name_ar,
                'city'    => $this->branch->city,
            ]),

            // تنسيق التواريخ
            'created_at' => $this->created_at->toDateString(),
            'updated_at' => $this->updated_at->toDateString(),
        ];
    }
}
