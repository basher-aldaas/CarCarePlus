<?php

namespace App\Enums;

enum AiRuleConditionType: string
{
    case CAR_BRAND = 'car_brand';
    case CAR_TYPE = 'car_type';
    case FUEL_TYPE = 'fuel_type';

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
