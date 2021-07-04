<?php namespace App\Services\Order\Constants;


use App\Helper\ConstGetter;

class SalesChannel
{
    use ConstGetter;

    const POS = 'pos';
    const WEBSTORE = 'webstore';

}
