<?php

namespace App\Services\Operations;

use App\DTOs\PointsConfigDTO;
use App\Models\PointsConfig;
use App\Repositories\Eloquent\PointsConfigRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PointsConfigService
{
    public function __construct(
        protected PointsConfigRepository $pointsConfigRepository
    ) {
    }

    public function index(): Collection
    {
        return $this->pointsConfigRepository->getAll();
    }

    public function show(int $id): PointsConfig
    {
        return $this->pointsConfigRepository->findById($id);
    }

    public function store(PointsConfigDTO $dto): PointsConfig
    {
        return DB::transaction(function () use ($dto) {
            return $this->pointsConfigRepository->create($dto);
        });
    }

    public function update(PointsConfig $pointsConfig, PointsConfigDTO $dto): PointsConfig
    {
        return DB::transaction(function () use ($pointsConfig, $dto) {
            return $this->pointsConfigRepository->update($pointsConfig, $dto);
        });
    }

    public function destroy(PointsConfig $pointsConfig): bool
    {
        return DB::transaction(function () use ($pointsConfig) {
            return $this->pointsConfigRepository->delete($pointsConfig);
        });
    }
}