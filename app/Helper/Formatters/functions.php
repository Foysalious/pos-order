<?php

use App\Helper\Formatters\TakaFormatter;

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
