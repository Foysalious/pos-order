<?php

if( !function_exists('reviewImageFolder')) {

    /**
     * Get Review Image folder Path.
     *
     * @param bool $with_base_url
     * @return string
     */

    function reviewImageFolder() : string {
        return 'images/collections/thumbs/';
    }
}
