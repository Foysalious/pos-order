<?php

use App\Services\Webstore\Order\States;
use App\Services\Webstore\Order\StateTags;

return [
    States::ORDER_PLACED => StateTags::ORDER_PLACED,
    States::ITEMS_PROCESSED => StateTags::ITEMS_PROCESSED,
    States::SHIPPED => StateTags::PRODUCT_DELIVERED,
    States::PRODUCT_DELIVERED => StateTags::PRODUCT_DELIVERED,
];
