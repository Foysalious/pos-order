<?php namespace App\Services\Usage;

use App\Helper\ConstGetter;

class Types
{
    use ConstGetter;

    const POS_ORDER_CREATE            = 'pos_order_create';
    const POS_ORDER_UPDATE            = 'pos_order_update';
    const POS_ORDER_DELETE            = 'pos_order_delete';
    const INVENTORY_CREATE            = 'inventory_create';
    const INVENTORY_UPDATE            = 'inventory_update';
    const INVENTORY_DELETE            = 'inventory_delete';
    const EXPENSE_TRACKER_TRANSACTION = 'expense_tracker_transaction';
    const SMS_MARKETING               = 'sms_marketing';
    const POS_DUE_COLLECTION          = 'pos_due_collection';
    const PRODUCT_LINK                = 'product_link';
    const PAYMENT_LINK                = 'payment_link';
    const DUE_TRACKER_TRANSACTION     = 'due_tracker_transaction';

    const TRANSACTION_COMPLETE        = 'transaction_complete';


    const CREATE_CUSTOMER             = 'customer_create';

    const PAYMENT_COLLECT             = 'payment_collect';
}
