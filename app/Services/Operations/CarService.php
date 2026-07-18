<?php

namespace App\Services\Operations;

use App\DTOs\CarDTO;
use App\Exceptions\CarDeleteUnauthorizedException;
use App\Exceptions\CarUpdateUnauthorizedException;
use App\Models\Car;
use App\Repositories\Eloquent\CarRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CarService
{
    public function __construct(protected CarRepository $carRepository)
    {}

    /**
     * @return Collection<int, Car>
     */
    public function getAllDashboardCars(): LengthAwarePaginator
    {
        return $this->carRepository->getAllDashboard()->paginate(10);
    }

    /**
     * @return Collection<int, Car>
     */
    public function getAllClientCars(int $customerId): Collection
    {
        return $this->carRepository->getAllClient($customerId);
    }
    public function createCar(CarDTO $DTO): Car
    {
        return $this->carRepository->create($DTO);
    }

    public function updateCar(CarDTO $DTO, int $id): Car
    {
        $car  = $this->carRepository->findById($id);
        $user = auth()->user();

        // Every user may update only their own cars; a super admin or admin may update any car.
        if ($car->user_id !== $user->id && ! $user->hasAnyRole(['super_admin', 'admin'])) {
            throw new CarUpdateUnauthorizedException();
        }

        return $this->carRepository->update($DTO, $id);
    }
    public function getCarById(int $id): Car
    {
        return $this->carRepository->findById($id);
    }

    public function deleteCar(int $id): void
    {
        $car  = $this->carRepository->findById($id);
        $user = auth()->user();

        // Only the car's owner or a super admin may delete it.
        if ($car->user_id !== $user->id && ! $user->hasRole('super_admin')) {
            throw new CarDeleteUnauthorizedException();
        }

        $this->carRepository->delete($id);
    }
}
