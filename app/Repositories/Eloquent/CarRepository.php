<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CarDTO;
use App\Models\Branch;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
class CarRepository
{
    /**
     * @return Collection<int, Car>
     */
    public function getAllDashboard(): Builder
    {

        $user = auth()->user();

        $query = Car::with(['owner', 'carType', 'branch'])
            ->latest();

        // مدير الفرع (admin) يرى فقط سيارات الفروع التي يديرها
        if ($user->hasRole('admin')) {
            $branchIds = Branch::where('admin_id', $user->id)->pluck('id');
            $query->whereIn('branch_id', $branchIds);
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
        return Car::with(['owner', 'carType', 'branch'])->findOrFail($id);
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
