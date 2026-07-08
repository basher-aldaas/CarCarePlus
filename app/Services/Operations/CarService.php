<?php

namespace App\Services\Operations;

use App\DTOs\CarDTO;
use App\Models\Car;
use App\Repositories\Eloquent\CarRepository;
use Illuminate\Database\Eloquent\Collection;

class CarService
{
    public function __construct(protected CarRepository $carRepository)
    {}

    /**
     * @return Collection<int, Car>
     */
    public function getAllDashboardCars(): Collection
    {
        return $this->carRepository->getAllDashboard();
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
        return $this->carRepository->update($DTO, $id);
    }
    public function getCarById(int $id): Car
    {
        return $this->carRepository->findById($id);
    }

    public function deleteCar(int $id): void
    {
        $this->carRepository->delete($id);
    }
}
