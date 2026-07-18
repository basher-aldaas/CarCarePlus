<?php

namespace App\Enums;

enum EmployeeType: string
{
    case WASHER   = 'washer';
    case MECHANIC = 'mechanic';
    case ADMIN    = 'admin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * The Spatie role name that corresponds to this employee type.
     */
    public function roleName(): string
    {
        return match ($this) {
            self::WASHER   => 'employee_washer',
            self::MECHANIC => 'employee_mechanic',
            self::ADMIN    => 'admin',
        };
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        return __("employee_type.{$this->value}", locale: $locale);
    }
}
