<?php


namespace App\Services\Operations;

use App\DTOs\WorkshopDTO;
use App\Models\Workshop;
use App\Repositories\Eloquent\WorkshopRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WorkshopService
{
    protected $workshopRepository;

    public function __construct(
        WorkshopRepository $workshopRepository
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
}
