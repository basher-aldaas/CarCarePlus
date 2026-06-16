<?php

namespace App\Enums\OrderEnums;

enum OrderMaterialStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case USED = 'used';

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
