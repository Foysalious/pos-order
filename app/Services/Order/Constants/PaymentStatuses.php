<?php namespace App\Services\Order\Constants;


use App\Helper\ConstGetter;

class PaymentStatuses
{
    use ConstGetter;
    const DUE = 'due';
    const PAID = 'paid';
}
