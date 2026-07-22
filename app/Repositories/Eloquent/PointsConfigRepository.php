<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PointsConfigDTO;
use App\Models\PointsConfig;
use Illuminate\Database\Eloquent\Collection;

class PointsConfigRepository
{
    public function getAll(): Collection
    {
        return PointsConfig::get();
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

        return $config;
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
