<?php

namespace App\Enums\OtpEnums;

enum OtpChannel: string
{
    case SMS = 'sms';
    case EMAIL = 'email';
    case WHATSAPP = 'whatsapp';

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
