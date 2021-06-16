<?php namespace App\Repositories\Accounting\Constants;

use App\Helper\ConstGetter;

class EntryTypes
{
    use ConstGetter;

    const DUE = "due";
    const DEPOSIT = "deposit";
    const INCOME = "income";
    const EXPENSE = "expense";
    const TRANSFER = "transfer";
    const INVENTORY = "inventory";
    const PAYMENT_LINK = "payment_link";
    const POS = "pos";
}
