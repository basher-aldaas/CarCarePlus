<?php

namespace App\Enums;

enum SuggestedProblemCategory: string
{
    case ENGINE = 'engine';
    case BRAKES = 'brakes';
    case ELECTRICAL = 'electrical';
    case TIRES = 'tires';
    case MECHANICAL = 'mechanical';
    case LOCKSMITH = 'locksmith';

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
