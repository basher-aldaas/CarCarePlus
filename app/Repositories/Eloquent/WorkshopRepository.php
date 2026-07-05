<?php

namespace App\Repositories\Eloquent;

use App\DTOs\WorkshopsDTOs\WorkshopDTO;
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
}
