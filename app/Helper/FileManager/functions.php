<?php
if (!function_exists('getBase64FileExtension')) {
    /**
     * getBase64FileExtension
     *
     * @param $file
     * @return string
     */
    function getBase64FileExtension($file)
    {
        return image_type_to_extension(getimagesize($file)[2], false);
    }
}

if (!function_exists('getFileName')) {
    function getFileName($file)
    {
        $extension = explode("/", $file);
        return end($extension);
    }
}
if (!function_exists('isAssoc')) {
    /**
     * @param $arr
     * @return bool
     */
    function isAssoc($arr)
    {
        if (!is_array($arr)) return false;
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
