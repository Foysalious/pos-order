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

if (!function_exists('clean')) {
    /**
     * @param $string
     * @param string $separator
     * @param array $keep
     * @return string|string[]|null
     */
    function clean($string, $separator = "-", $keep = [])
    {
        $string    = str_replace(' ', $separator, $string); // Replaces all spaces with hyphens.
        $keep_only = "/[^A-Za-z0-9";
        foreach ($keep as $item) {
            $keep_only .= "$item";
        }
        $keep_only .= (($separator == '-') ? '\-' : "_");
        $keep_only .= "]/";
        $string    = preg_replace($keep_only, '', $string);           // Removes special chars.
        return preg_replace("/$separator+/", $separator, $string);    // Replaces multiple hyphens with single one.
    }
}

if (!function_exists('generateRandomFileName')) {
    function generateRandomFileName($length) : string
    {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return $key;
    }
}

if (! function_exists('camel_case')) {
    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    function camel_case($value)
    {
        return Str::camel($value);
    }
}

if (!function_exists('pamelCase')) {
    /**
     * @param $string
     * @return string
     */
    function pamelCase($string)
    {
        return ucfirst(camel_case($string));
    }
}
