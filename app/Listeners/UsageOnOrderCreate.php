<?php namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Usage\Types;
use App\Services\Usage\UsageService;

class UsageOnOrderCreate
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(protected UsageService $usageService){}

    /**
     * Handle the event.
     *
     * @param OrderCreated $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        $usage_type = $event->getOrder()->sales_channel_id == SalesChannelIds::WEBSTORE ? Types::PRODUCT_LINK : Types::POS_ORDER_CREATE;
        $this->usageService->setUserId((int) $event->getOrder()->partner_id)->setUsageType($usage_type)->store();
    }
}
