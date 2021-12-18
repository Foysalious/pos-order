<?php namespace App\Services\Discount\Constants;

use App\Helper\ConstGetter;
use Illuminate\Validation\ValidationException;

class DiscountTypes
{
    use ConstGetter;

    const ORDER = 'order';
    const ORDER_SKU = 'order_sku';
    const VOUCHER = 'voucher';

    /**
     * @param $type
     * @throws ValidationException
     */
    public static function checkIfValid($type)
    {
        if (!in_array($type, self::get())) throw new ValidationException($type, "'$type' is not a valid discount type.");
    }

}
