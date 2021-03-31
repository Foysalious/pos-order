<?php namespace App\Services\Discount\Constants;

use App\Helper\ConstGetter;

class DiscountTypes
{
    use ConstGetter;

    const ORDER    = 'order';
    const SERVICE  = 'service';
    const VOUCHER  = 'voucher';

}
