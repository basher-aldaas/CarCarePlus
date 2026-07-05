<?php

namespace App\Enums;

enum WorkshopStatus: string
{
    // Registration lifecycle states
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    // Operational states (post-approval)
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        return __("device_status.{$this->value}", locale: $locale);
    }
}
