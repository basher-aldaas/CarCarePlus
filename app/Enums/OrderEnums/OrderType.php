<?php

namespace App\Enums\OrderEnums;

enum OrderType: string
{
    case SERVICE = 'service';
    case ROAD_ASSISTANCE = 'road_assistance';

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
