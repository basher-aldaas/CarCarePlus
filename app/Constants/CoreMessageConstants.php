<?php

namespace App\Constants;

use Illuminate\Support\Facades\Lang;

class CoreMessageConstants
{
    public const GENERIC_SUCCESS = 'messages.generic.success';
    public const GENERIC_ERROR = 'messages.generic.error';
    public const VALIDATION_FAILED = 'messages.generic.validation_failed';

    /**
     * Get a localized message
     *
     * @template TReplace of array
     *
     * @param  string  $key  The message key
     * @param  TReplace  $replace  The replacement values
     * @return string The localized message
     */
    public static function get(string $key, array $replace = []): string
    {
        return Lang::get($key, $replace);
    }
}
