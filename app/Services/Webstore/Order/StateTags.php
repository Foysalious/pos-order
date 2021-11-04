<?php

namespace App\Services\Webstore\Order;

use App\Helper\ConstGetter;

class StateTags
{
    use ConstGetter;

    const ORDER_PLACED = 'order_placed';
    const ITEMS_PROCESSED = 'items_processed';
    const SHIPPED = 'shipped';
    const PRODUCT_DELIVERED = 'product_delivered';

}
