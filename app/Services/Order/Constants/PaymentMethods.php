<?php namespace App\Services\Order\Constants;

use App\Helper\ConstGetter;

class PaymentMethods
{
    use ConstGetter;
    const PAYMENT_LINK = 'payment_link';
    const CASH_ON_DELIVERY = 'cod';
    const BKASH = 'bkash';
    const ONLINE = 'online';
    const QR_CODE = 'qr_code';
    const ADVANCE_BALANCE = 'advance_balance';
    const EMI = 'emi';
    const OTHERS = 'others';
}
