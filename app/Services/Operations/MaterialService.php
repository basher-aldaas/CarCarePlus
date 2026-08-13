<?php

namespace App\Services\Operations;

use App\DTOs\MaterialDTO;
use App\Models\Material;
use App\Repositories\Eloquent\MaterialRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MaterialService
{
    public function __construct(protected MaterialRepository $materialRepository)
    {}
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->materialRepository->getAll($perPage);
    }

    public function show(Material $material): Material
    {
        $user = auth()->user();

        if ($user && $user->hasAnyRole(['customer_personal', 'customer_company'])) {
            abort_unless(
                $material->is_visible_to_customer,
                404,
                __('Material not found')
            );
        }

        return $this->materialRepository->findById($material);
    }

    public function store(MaterialDTO $dto): Material
    {
        return DB::transaction(function () use($dto){
            return $this->materialRepository->create($dto);
        });
    }

    public function update(Material $material, MaterialDTO $dto): Material
    {
        return DB::transaction(function () use ($material, $dto){
            return $this->materialRepository->update($material, $dto);
        });
    }

    public function delete(Material $material): bool
    {
        return DB::transaction(function () use($material){
            return $this->materialRepository->delete($material);
        });
    }
}
