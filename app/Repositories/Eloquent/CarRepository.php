<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CarsDTOs\CreateCarDTO;
use App\DTOs\CarsDTOs\UpdateCarDTO;
use App\Models\Car;
use Illuminate\Database\Eloquent\Collection;

class CarRepository
{
    /**
     * جميع سيارات المستخدم الحالي
     */
    public function getUserCars(int $customerId): Collection
    {
        return Car::with(['company', 'carType'])
            ->where('customer_id', $customerId)
            ->latest()
            ->get();
    }

    /**
     * البحث عن سيارة يملكها المستخدم
     */
    public function findUserCar(int $customerId, int $carId): ?Car
    {
        return Car::where('customer_id', $customerId)
            ->where('id', $carId)
            ->first();
    }

    /**
     * إنشاء سيارة
     */
    public function create(CreateCarDTO $DTO): Car
    {
        return Car::create($DTO->toArray());
    }

    /**
     * تعديل سيارة
     */
    public function update(Car $car, UpdateCarDTO $DTO): Car
    {
        $car->update($DTO->toArray());

        return $car->refresh();
    }

    /**
     * حذف سيارة
     */
    public function delete(Car $car): bool
    {
        // احذف الصورة إذا كانت موجودة
        if ($car->image_url) {
            \Storage::disk('public')->delete($car->image_url);
        }

        return $car->delete();
    }
}
