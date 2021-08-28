<?php namespace App\Services\Webstore\Order;

use App\Helper\ConstGetter;

class Statuses
{
    use ConstGetter;

    const ORDER_PLACED = 'Order placed';
    const ITEMS_PROCESSED = 'Items Processed';
    const SHIPPED = 'Shipped';
    const PRODUCT_DELIVERED = 'Product Delivered';
}
