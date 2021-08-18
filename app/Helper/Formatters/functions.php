<?php

use App\Helper\Formatters\TakaFormatter;
use Carbon\Carbon;

if (!function_exists('formatTakaToDecimal')) {
    /**
     * Format integer amount of taka into decimal.
     *
     * @param  $amount
     * @param  $comma_separation
     * @param  $comma_separation_format
     * @return string
     */
    function formatTakaToDecimal($amount, $comma_separation = false, $comma_separation_format = "BDT")
    {
        return TakaFormatter::toDecimal($amount, $comma_separation, $comma_separation_format);
    }
}

if (!function_exists('convertTimezone')) {

    /**
     * @param Carbon|null $datetime
     * @param string $timezone
     * @return Carbon|null
     */
    function convertTimezone(?Carbon $datetime, string $timezone = 'Asia/Dhaka'): ?Carbon
    {
        if (!$datetime) return null;
        return $datetime->timezone($timezone);

    }
}
