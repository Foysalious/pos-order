<?php


namespace App\Services\Transaction\Constants;


use App\Helper\ConstGetter;

class TransactionTypes
{
    use ConstGetter;

    const DEBIT = 'debit';
    const CREDIT = 'credit';
}
