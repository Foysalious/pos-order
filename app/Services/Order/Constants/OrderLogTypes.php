<?php namespace App\Services\Order\Constants;


use App\Helper\ConstGetter;

class OrderLogTypes
{
    use ConstGetter;

    const SKU = 'sku';
    const ORDER_STATUS = 'order_status';
    const CUSTOMER = 'customer';
    const OTHERS = 'others';
    const INIT = 'init';
}
