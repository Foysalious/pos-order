<?php

use App\Services\Webstore\Order\States;
use App\Services\Webstore\Order\StateTags;

return [
    States::ORDER_PLACED => StateTags::ORDER_PLACED,
    States::ITEMS_PROCESSED => StateTags::ITEMS_PROCESSED,
    States::SHIPPED => StateTags::SHIPPED,
    States::PRODUCT_DELIVERED => StateTags::PRODUCT_DELIVERED,
];
