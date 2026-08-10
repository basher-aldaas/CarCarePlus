<?php

namespace App\Support;

/**
 * Great-circle distance between two lat/lng points, in kilometres. Computed
 * in PHP (not SQL) so it works identically across every DB driver the app
 * runs on, including the sqlite database used in tests.
 */
class GeoDistance
{
    protected const EARTH_RADIUS_KM = 6371;

    public static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return round(self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }
}