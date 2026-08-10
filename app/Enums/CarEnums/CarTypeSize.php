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

    /**
     * Maps a CarType's name (e.g. "Truck / Pickup") to the closest
     * CarTypeSize case, since the two aren't 1:1. Falls back to SEDAN
     * when nothing matches (e.g. "Luxury / Sports").
     */
    public static function fromCarTypeName(string $name): self
    {
        $name = strtolower($name);

        return match (true) {
            str_contains($name, 'suv') => self::SUV,
            str_contains($name, 'hatchback') => self::HATCHBACK,
            str_contains($name, 'pickup'), str_contains($name, 'truck') => self::PICKUP,
            default => self::SEDAN,
        };
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        return __("device_status.{$this->value}", locale: $locale);
    }
}
