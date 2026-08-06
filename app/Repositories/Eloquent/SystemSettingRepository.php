<?php

namespace App\Repositories\Eloquent;

use App\DTOs\SystemSettingDTO;
use App\Models\SystemSetting;
use Illuminate\Pagination\LengthAwarePaginator;

class SystemSettingRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return SystemSetting::paginate($perPage);
    }

    public function findById(SystemSetting $systemSetting): SystemSetting
    {
        return $systemSetting;
    }

    public function create(SystemSettingDTO $dto): SystemSetting
    {
        return SystemSetting::create($dto->toArray());
    }

    public function update(SystemSetting $systemSetting, SystemSettingDTO $dto): SystemSetting
    {
        $systemSetting->update($dto->toArray());
        return $systemSetting->fresh();
    }

    public function delete(SystemSetting $systemSetting): bool
    {
        return $systemSetting->delete();
    }
}
