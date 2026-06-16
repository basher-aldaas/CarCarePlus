<?php

namespace App\Enums\OrderEnums;

enum OrderSubServiceStatus: string
{
    case PENDING = 'pending';
    case DONE = 'done';
    case SKIPPED = 'skipped';

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
