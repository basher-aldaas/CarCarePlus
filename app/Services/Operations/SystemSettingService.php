<?php

namespace App\Services\Operations;

use App\DTOs\SystemSettingDTO;
use App\Models\SystemSetting;
use App\Repositories\Eloquent\SystemSettingRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SystemSettingService
{
    public function __construct(protected SystemSettingRepository $systemSettingRepository)
    {}
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->systemSettingRepository->getAll($perPage);
    }

    public function show(SystemSetting $systemSetting): SystemSetting
    {
        return $this->systemSettingRepository->findById($systemSetting);
    }

    public function store(SystemSettingDTO $dto): SystemSetting
    {
        return DB::transaction(function () use($dto){
            return $this->systemSettingRepository->create($dto);
        });
    }

    public function update(SystemSetting $systemSetting, SystemSettingDTO $dto): SystemSetting
    {
        return DB::transaction(function () use ($systemSetting, $dto){
            return $this->systemSettingRepository->update($systemSetting, $dto);
        });
    }

    public function delete(SystemSetting $systemSetting): bool
    {
        return DB::transaction(function () use($systemSetting){
            return $this->systemSettingRepository->delete($systemSetting);
        });
    }
}
