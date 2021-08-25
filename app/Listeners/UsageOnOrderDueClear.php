<?php namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Usage\Types;
use App\Services\Usage\UsageService;


class UsageOnOrderDueClear
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
        $this->usageService->setUserId($event->getOrder()->partner->id)->setUsageType(Types::POS_DUE_COLLECTION)->store();
    }
}
