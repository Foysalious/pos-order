<?php namespace App\Services\Order\Constants;

use App\Helper\ConstGetter;

class PaymentMethods
{
    use ConstGetter;
    const PAYMENT_LINK = 'payment_link';
    const CASH_ON_DELIVERY = 'cod';
}
