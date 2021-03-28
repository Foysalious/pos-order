<?php

if (!function_exists('normalizeStringCases')) {
    /**
     * @param $value
     * @return string
     */
    function normalizeStringCases($value)
    {
        $value = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
        return ucwords(str_replace(['_','-'], ' ', $value));
    }
}
