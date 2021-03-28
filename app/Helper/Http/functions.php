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

if (!function_exists('calculatePagination')) {
    /**
     * @param $request
     * @return array
     */
    function calculatePagination($request)
    {
        $offset = $request->has('offset') ? $request->offset : 0;
        $limit = $request->has('limit') ? $request->limit : 50;
        return [$offset, $limit];
    }
}
