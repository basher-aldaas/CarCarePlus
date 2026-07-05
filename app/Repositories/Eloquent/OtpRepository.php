<?php

namespace App\Repositories\Eloquent;

use App\DTOs\OtpDTOs\OtpDTO;
use App\Enums\OtpEnums\OtpType;
use App\Models\OtpCode;

class OtpRepository
{
    public function create(OtpDTO $dto): OtpCode
    {
        return OtpCode::create($dto->toArray());
    }

    /**
     * The most recent code of a given type for a user, regardless of state.
     * Used for resend-cooldown checks.
     */
    public function latestForType(int $userId, OtpType $type): ?OtpCode
    {
        return OtpCode::where('user_id', $userId)
            ->where('type', $type)
            ->latest()
            ->first();
    }

    /**
     * The most recent still-usable (unused) code of a given type for a user.
     */
    public function latestActiveForType(int $userId, OtpType $type): ?OtpCode
    {
        return OtpCode::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_used', false)
            ->latest()
            ->first();
    }

    /**
     * Invalidate every outstanding (unused) code of a type for a user.
     */
    public function invalidateActive(int $userId, OtpType $type): void
    {
        OtpCode::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true]);
    }
}
