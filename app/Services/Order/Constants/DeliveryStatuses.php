<?php

namespace App\Services\Order\Constants;

use App\Helper\ConstGetter;

class DeliveryStatuses
{
    use ConstGetter;
    const PICKED_UP = 'Picked up';
    const DELIVERED = 'Delivered';

}
