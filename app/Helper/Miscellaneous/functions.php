<?php

if (!function_exists('simplifyExceptionTrace')) {
    /**
     * @param \Exception $e
     * @return array
     */
    function simplifyExceptionTrace(\Exception $e)
    {
        return collect(explode(PHP_EOL, $e->getTraceAsString()))->mapWithKeys(function ($trace) {
            $trace = explode(": ", preg_replace('/^(#\d+ )(.*)$/', '$2', $trace));
            if (count($trace) == 1) $trace[1] = "";
            return [$trace[0] => $trace[1]];
        })->all();
    }
}

if (!function_exists('array_push_on_array')) {
    /**
     * @param array $array
     * @param $key
     * @param $value
     */
    function array_push_on_array(array &$array, $key, $value)
    {
        if (!array_key_exists($key, $array)) $array[$key] = [];

        $array[$key][] = $value;
    }
}

