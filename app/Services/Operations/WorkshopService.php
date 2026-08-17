<?php


namespace App\Services\Operations;

use App\DTOs\WorkshopDTO;
use App\Exceptions\CarHistoryUnauthorizedException;
use App\Exceptions\WorkshopNotFoundForOwnerException;
use App\Models\Order;
use App\Models\Workshop;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\WorkshopRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WorkshopService
{
    protected $workshopRepository;

    public function __construct(
        WorkshopRepository $workshopRepository,
        protected OrderRepository $orderRepository,
    )
    {
        $this->workshopRepository = $workshopRepository;
    }

    /**
     * Get all workshops.
     *
     * @return Collection<int, Workshop>
     */
    public function index(): Collection
    {
        return $this->workshopRepository->getAll();
    }

    /**
     * Users eligible to own a new workshop (see WorkshopRepository::ownerCandidates).
     *
     * @return Collection<int, \App\Models\User>
     */
    public function ownerCandidates(): Collection
    {
        return $this->workshopRepository->ownerCandidates();
    }

    /**
     * Show one workshop.
     */
    public function show(int $id): Workshop
    {
        return $this->workshopRepository->findById($id);
    }

    /**
     * Create workshop.
     */
    public function store(WorkshopDTO $dto): Workshop
    {
        return DB::transaction(function () use ($dto) {

            return $this->workshopRepository->create($dto);

        });
    }

    /**
     * Update workshop.
     */
    public function update(
        Workshop    $workshop,
        WorkshopDTO $dto
    ): Workshop
    {

        return DB::transaction(function () use ($workshop, $dto) {

            return $this->workshopRepository
                ->update($workshop, $dto);

        });
    }

    /**
     * Delete workshop.
     */
    public function destroy(Workshop $workshop): bool
    {
        return DB::transaction(function () use ($workshop) {

            return $this->workshopRepository
                ->delete($workshop);

        });
    }

    /**
     * Get workshop of logged-in owner.
     */
    public function myWorkshop(int $ownerId)
    {
        return $this->workshopRepository
            ->findByOwnerId($ownerId);
    }

    /**
     * Active workshops within radiusKm of the given point, nearest first —
     * used by customers to pick a workshop when booking maintenance.
     *
     * @return Collection<int, Workshop>
     */
    public function nearby(float $lat, float $lng, float $radiusKm): Collection
    {
        return $this->workshopRepository->nearby($lat, $lng, $radiusKm);
    }

    /**
     * A car's Maintenance + Roadside Assistance history, for the workshop
     * manager currently logged in. Only cars that have actually had an
     * order at this workshop are visible — a workshop can't browse an
     * arbitrary car's record just because it knows the id.
     *
     * @return Collection<int, Order>
     */
    public function carHistoryFor(int $ownerId, int $carId): Collection
    {
        $workshop = $this->workshopRepository->findByOwnerId($ownerId);

        if (! $workshop) {
            throw new WorkshopNotFoundForOwnerException();
        }


        return $this->orderRepository->carServiceHistory($carId);
    }
}
