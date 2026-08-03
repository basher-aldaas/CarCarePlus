<?php

namespace App\Repositories\Eloquent;

use App\DTOs\WorkshopDTO;
use App\Enums\WorkshopStatus;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Collection;

class WorkshopRepository
{
    public function create(WorkshopDTO $DTO): Workshop
    {
        return Workshop::create($DTO->toArray());
    }

    /**
     * @return Collection<int, Workshop>
     */
    public function pending(): Collection
    {
        return Workshop::with('owner')
            ->where('status', WorkshopStatus::PENDING)
            ->latest()
            ->get();
    }

    public function getAll(): Collection
    {
        return Workshop::with('owner')
            ->latest()
            ->get();
    }

    public function findById(int $id): Workshop
    {
        return Workshop::with('owner')->findOrFail($id);
    }

    public function update(Workshop $workshop, WorkshopDTO $dto): Workshop
    {
        $workshop->update($dto->toArray());

        return $workshop->refresh();
    }

    public function delete(Workshop $workshop): bool
    {
        return $workshop->delete();
    }

    public function findByOwnerId(int $ownerId)
    {
        return Workshop::where('user_id', $ownerId)->first();
    }


}
