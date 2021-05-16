<?php
if (!function_exists('calculatePagination')) {
    /**
     * @param $request
     * @return array
     */
    function calculatePagination($request)
    {
        $offset = $request->has('offset') ? $request->offset : 0;
        $limit  = $request->has('limit') ? $request->limit : 10;
        return [$offset, $limit];
    }
}
