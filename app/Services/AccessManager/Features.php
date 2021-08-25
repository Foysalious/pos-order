<?php namespace App\Services\AccessManager;


use App\Helper\ConstGetter;

class Features
{
    use ConstGetter;

    const PRODUCT_WEBSTORE_PUBLISH = 'product_webstore_publish';
    const INVOICE_DOWNLOAD = 'invoice_download';
}
