<?php


namespace App\Services\Operations;

use App\Models\Service;

class AIRecommendationService
{
    public function findService(?string $serviceName): ?Service
    {
        if (!$serviceName) {
            return null;
        }

        return Service::query()
            ->where(function ($query) use ($serviceName) {

                $query
                    ->where('name', 'like', "%{$serviceName}%")
                    ->orWhere('name_ar', 'like', "%{$serviceName}%");

            })
            ->first();
    }
}
