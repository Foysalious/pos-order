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
}
