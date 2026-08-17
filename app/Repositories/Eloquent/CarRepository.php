<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CarDTO;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filters\CarFilter;
class CarRepository
{
    /**
     * @return Collection<int, Car>
     */
    public function getAllDashboard(?CarFilter $filter = null): Builder
    {
        $query = Car::with(['owner', 'carType', 'brand'])
            ->latest();

        if ($filter) {
            $query = $filter->apply($query);
        }

        return $query;
    }

    /**
     * @return Collection<int, Car>
     */
    public function getAllClient(int $customerId): Collection
    {
        return Car::with(['carType'])
            ->where('user_id', $customerId)
            ->latest()
            ->get();
    }

    public function create(CarDTO $DTO): Car
    {
        return Car::create($DTO->toArray());
    }

    public function update(CarDTO $DTO, int $id): Car
    {
        $car = Car::findOrFail($id);

        $car->update($DTO->toArray());

        return $car->refresh();
    }

    public function findById(int $id): Car
    {
        return Car::with(['owner', 'carType', 'brand'])
            ->findOrFail($id);
    }

    public function delete(int $id): void
    {
        $car = Car::findOrFail($id);

        if ($car->image_url) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($car->image_url);
        }

        $car->delete();
    }

}
