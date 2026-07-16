<?php


namespace App\Services\Operations;

use App\DTOs\CarsDTOs\CreateCarDTO;
use App\DTOs\CarsDTOs\UpdateCarDTO;
use App\Models\Car;
use App\Repositories\Eloquent\CarRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CarService
{

    protected $carRepository;

    public function __construct(

        CarRepository $carRepository
    ) {
        $this->carRepository = $carRepository;
    }

    /**
     * Show authenticated user's vehicles.
     */
    public function index()
    {

        return $this->carRepository->getUserCars(auth()->id());
    }

    /**
     * Store new vehicle.
     */
    public function store(CreateCarDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {

            $car = $this->carRepository->create($dto);

            return [
                'car' => $car->refresh(),  //               'car' => $car,

            ];
        });
    }

    /**
     * Update vehicle.
     */
    public function update(int $id, UpdateCarDTO $dto): array
    {
        return DB::transaction(function () use ($id, $dto) {

            $car = $this->carRepository
                ->findUserCar(auth()->id(), $id);

            if (!$car) {
                throw new NotFoundHttpException('Vehicle not found.');
            }
//            $car = $this->carRepository
//                ->update($car, $dto);
            $this->carRepository->update($car, $dto);

            return [
                'car' => $car->fresh(), // 'car' => $car
            ];
        });
    }

    /**
     * Delete vehicle.
     */
    public function destroy(int $id)
    {
        DB::transaction(function () use ($id) {

            $car = $this->carRepository
                ->findUserCar(auth()->id(), $id);

            if (!$car) {
                throw new NotFoundHttpException('Vehicle not found.');
            }

            $this->carRepository->delete($car);

        });
    }
}
