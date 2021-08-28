<?php namespace App\Services\Accounting\Constants;

use App\Helper\ConstGetter;

class OrderChangingTypes
{
    use ConstGetter;
    const REFUND = 'refund';
    const EXCHANGE = 'exchange';
    const QUANTITY_DECREASE = 'quantity_decrease';
    const QUANTITY_INCREASE = 'quantity_increase';
    const NEW = 'new';
}
