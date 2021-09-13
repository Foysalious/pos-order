<?php

if (!function_exists('getValidationErrorMessage')) {
    /**
     * @param $errors
     * @return string
     */
    function getValidationErrorMessage($errors)
    {
        $msg = '';
        foreach ($errors as $key => $error) {
            $msg .= $error;
        }
        return $msg;
    }
}
