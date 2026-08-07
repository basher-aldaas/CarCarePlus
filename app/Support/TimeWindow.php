<?php

namespace App\Support;

class TimeWindow
{
    /**
     * Whether $time ("H:i") falls within [$start, $end] ("H:i"). The
     * window may cross midnight (e.g. 22:00-06:00).
     */
    public static function contains(string $time, string $start, string $end): bool
    {
        return $start <= $end
            ? ($time >= $start && $time <= $end)
            : ($time >= $start || $time <= $end);
    }
}
