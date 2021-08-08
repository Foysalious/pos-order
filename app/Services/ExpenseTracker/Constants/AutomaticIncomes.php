<?php namespace Sheba\ExpenseTracker;

use ReflectionClass;

class AutomaticIncomes
{
    const MARKET_PLACE = 'Marketplace sales';
    const POS          = 'POS sales';
    const TOP_UP       = 'Mobile Recharge';
    const MOVIE_TICKET = 'Movie ticket sales';
    const BUS_TICKET   = 'Bus ticket sales';
    const PAYMENT_LINK = 'Payment link';
    const OTHER_INCOME = 'Other Income';
    const DUE_TRACKER  = 'Due Tracker';
    const WEBSTORE_SALES = 'Webstore sales';

    public static function heads()
    {
        return array_values((new ReflectionClass(__CLASS__))->getConstants());
    }
}
