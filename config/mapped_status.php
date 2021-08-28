<?php

use App\Services\Order\Constants\Statuses;
use App\Services\Webstore\Order\Statuses as WebStoreStatuses;

return [
    Statuses::PROCESSING => WebStoreStatuses::ITEMS_PROCESSED,
    Statuses::SHIPPED => WebStoreStatuses::SHIPPED,
    Statuses::COMPLETED => WebStoreStatuses::PRODUCT_DELIVERED,
];
