<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PointsConfigDTO;
use App\Models\PointsConfig;
use Illuminate\Pagination\LengthAwarePaginator;

class PointsConfigRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return PointsConfig::paginate($perPage);
    }

    public function findById(int $id): PointsConfig
    {
        return PointsConfig::findOrFail($id);
    }

    public function create(PointsConfigDTO $dto): PointsConfig
    {
        $data = $dto->toArray();

        $config = PointsConfig::create($data);

        if ($config->is_active) {
            $this->deactivateOthers($config->id);
        }

        return $config->fresh();
    }

    public function update(PointsConfig $pointsConfig, PointsConfigDTO $dto): PointsConfig
    {
        $pointsConfig->update($dto->toArray());

        if ($pointsConfig->is_active) {
            $this->deactivateOthers($pointsConfig->id);
        }

        return $pointsConfig->fresh();
    }

    public function delete(PointsConfig $pointsConfig): bool
    {
        return $pointsConfig->delete();
    }

    private function deactivateOthers(int $exceptId): void
    {
        PointsConfig::where('id', '!=', $exceptId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
