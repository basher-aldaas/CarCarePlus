<?php

namespace App\Repositories\Eloquent;

use App\DTOs\WorkshopDTO;
use App\Enums\WorkshopStatus;
use App\Models\Workshop;
use App\Support\GeoDistance;
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

    public function findByIdOrNull(int $id): ?Workshop
    {
        return Workshop::find($id);
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

    /**
     * Active workshops within radiusKm of the given point, nearest first.
     * Distance is computed in PHP (not SQL) so this works the same across
     * every DB driver the app runs on (including sqlite in tests).
     *
     * @return Collection<int, Workshop>
     */
    public function nearby(float $lat, float $lng, float $radiusKm): Collection
    {
        return Workshop::where('status', WorkshopStatus::ACTIVE)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (Workshop $workshop) use ($lat, $lng) {
                $workshop->distance_km = GeoDistance::km(
                    $lat,
                    $lng,
                    (float) $workshop->latitude,
                    (float) $workshop->longitude,
                );

                return $workshop;
            })
            ->filter(fn (Workshop $workshop) => $workshop->distance_km <= $radiusKm)
            ->sortBy('distance_km')
            ->values();
    }
}
