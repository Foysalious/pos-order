<?php namespace App\Services\Accounting\Constants;

use App\Helper\ConstGetter;

class Sales
{
    use ConstGetter;

    const SALES_FROM_POS = 'sales_from_pos';
    const SALES_FROM_ECOM = 'sales_from_ecom';
}
