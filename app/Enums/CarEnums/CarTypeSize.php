<?php

namespace App\Enums\CarEnums;

enum CarTypeSize: string
{
    case SEDAN = 'sedan';
    case SUV = 'suv';
    case HATCHBACK = 'hatchback';
    case PICKUP = 'pickup';

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
