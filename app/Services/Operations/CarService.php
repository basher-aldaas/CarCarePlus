<?php

namespace App\Services\Operations;

use App\DTOs\CarsDTOs\CreateCarDTO;
use App\DTOs\CarsDTOs\UpdateCarDTO;
use App\Repositories\Eloquent\CarRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CarService
{
    protected $carRepository;

    public function __construct(
        CarRepository $carRepository
    ) {
        $this->carRepository = $carRepository;
    }

    public function index()
    {
        return $this->carRepository->getUserCars(auth()->id());
    }

    public function store(CreateCarDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {

            $car = $this->carRepository->create($dto);

            return [
                'car' => $car->refresh(),
            ];
        });
    }

    public function update(int $id, UpdateCarDTO $dto): array
    {
        return DB::transaction(function () use ($id, $dto) {

            $car = $this->carRepository
                ->findUserCar(auth()->id(), $id);

            if (!$car) {
                throw new NotFoundHttpException('Vehicle not found.');
            }

            $this->carRepository->update($car, $dto);

            return [
                'car' => $car->fresh(),
            ];
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {

            $car = $this->carRepository
                ->findUserCar(auth()->id(), $id);

            if (!$car) {
                throw new NotFoundHttpException('Vehicle not found.');
            }

            if ($car->image_url) {
                Storage::disk('public')->delete($car->image_url);
            }

            $this->carRepository->delete($car);

        });
    }
}
