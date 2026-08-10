<?php

namespace App\Repositories\Eloquent;

use App\DTOs\BranchesDTO;
use App\Models\Branch;
use App\Support\GeoDistance;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Branch::with('manager')->paginate($perPage);
    }

    public function findById(Branch $branch): Branch
    {
        return $branch->load('manager');
    }

    public function findByIdOrNull(int $id): ?Branch
    {
        return Branch::find($id);
    }

    /**
     * The active branch closest to the given point, or null if no active
     * branch has coordinates. Distance is computed in PHP so it behaves the
     * same across every DB driver (see GeoDistance).
     */
    public function nearest(float $lat, float $lng): ?Branch
    {
        return Branch::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->sortBy(fn (Branch $branch) => GeoDistance::km(
                $lat,
                $lng,
                (float) $branch->latitude,
                (float) $branch->longitude,
            ))
            ->first();
    }

    public function create(BranchesDTO $dto): Branch
    {
        return Branch::create($dto->toArray());
    }

    public function update(Branch $branch, BranchesDTO $dto): Branch
    {
        $branch->update($dto->toArray());
        return $branch->fresh('manager');
    }

    public function delete(Branch $branch): bool
    {
        return $branch->delete();
    }
}
