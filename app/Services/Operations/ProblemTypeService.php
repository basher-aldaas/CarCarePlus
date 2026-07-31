<?php

namespace App\Services\Operations;

use App\DTOs\ProblemTypeDTO;
use App\Models\ProblemType;
use App\Repositories\Eloquent\ProblemTypeRepository;
use Illuminate\Support\Facades\DB;

class ProblemTypeService
{
    public function __construct(protected ProblemTypeRepository $problemTypeRepository)
    {}
    public function index()
    {
        return $this->problemTypeRepository->getAll();
    }

    public function show(ProblemType $problemType): ProblemType
    {
        return $this->problemTypeRepository->findById($problemType);
    }

    public function store(ProblemTypeDTO $dto): ProblemType
    {
        return DB::transaction(function () use($dto){
            return $this->problemTypeRepository->create($dto);
        });
    }

    public function update(ProblemType $problemType, ProblemTypeDTO $dto): ProblemType
    {
        return DB::transaction(function () use ($problemType, $dto){
            return $this->problemTypeRepository->update($problemType, $dto);
        });
    }

    public function delete(ProblemType $problemType): bool
    {
        return DB::transaction(function () use($problemType){
            return $this->problemTypeRepository->delete($problemType);
        });
    }
}
