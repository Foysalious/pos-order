<?php namespace App\Services\Order\Constants;


use App\Helper\ConstGetter;

class OrderLogTypes
{
    use ConstGetter;

    const PRODUCTS_AND_PRICES = 'products_and_prices';
    const ORDER_STATUS = 'order_status';
    const CUSTOMER = 'customer';
    const OTHERS = 'others';
}
