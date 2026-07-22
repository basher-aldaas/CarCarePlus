<?php

namespace App\Repositories\Eloquent;

use App\DTOs\PackageDTO;
use App\Models\Package;

class PackageRepository
{
    public function getAll()
    {
        return Package::latest()->get();
    }

    public function findById(int $id): Package
    {
        return Package::findOrFail($id);
    }

    public function create(PackageDTO $dto): Package
    {
        return Package::create($dto->toArray());
    }

    public function update(Package $package, PackageDTO $dto): Package
    {
        $package->update($dto->toArray());

        return $package->fresh();
    }

    public function delete(Package $package): bool
    {
        return $package->delete();
    }
}