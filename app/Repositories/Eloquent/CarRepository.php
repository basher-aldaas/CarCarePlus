<?php

namespace App\Repositories\Eloquent;

    use App\DTOs\CarsDTOs\CreateCarDTO;
    use App\DTOs\CarsDTOs\UpdateCarDTO;
    use App\Models\Car;
    use Illuminate\Database\Eloquent\Collection;

class CarRepository
{
    /**
     * Return all cars.
     */
    public function all():Collection
    {
        return Car::all();
    }

    public function getUserCars(int $customerId): Collection
    {
        return Car::where('customer_id',$customerId)
            ->latest()
            ->get();
    }

    public function findUserCar(
        int $customerId,
        int $carId
    )
    {
        return Car::where('customer_id',$customerId)
            ->where('id',$carId)
            ->first();
    }


    /**
     * Create a new car.
     */
    public function create(CreateCarDTO $DTO): Car
    {
        return Car::create($DTO->toArray());
    }

    /**
     * Update existing car.
     */
    public function update(Car $car, UpdateCarDTO $DTO): Car
    {
        $car->update($DTO->toArray());

        return $car->refresh();
    }

    /**
     * Delete a car.
     */
    public function delete(Car $car)
    {
        return  $car->delete();
    }

}
