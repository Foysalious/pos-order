<?php namespace App\Services\Order\Constants;

use App\Helper\ConstGetter;

class Statuses
{
    use ConstGetter;

    const PENDING = 'Pending';
    const PROCESSING = 'Processing';
    const DECLINED = 'Declined';
    const SHIPPED = 'Shipped';
    const COMPLETED = 'Completed';
    const CANCELLED = 'Cancelled';

    const PAYMENT_STATUS = ['paid' => 'paid', 'due' => 'due'];
    const ORDER_FILTER_TYPE = ['new' => 'new', 'running' => 'running', 'completed' => 'completed'];
}
