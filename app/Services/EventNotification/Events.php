<?php namespace App\Services\EventNotification;

use App\Helper\ConstGetter;

class Events
{
    use ConstGetter;

    const ORDER_CREATE = 'order_create';
    const ORDER_UPDATE = 'order_update';
    const ORDER_DELETE = 'order_delete';
}
