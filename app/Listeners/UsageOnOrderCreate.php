<?php namespace App\Listeners;

use App\Events\OrderTransactionCompleted;
use App\Jobs\Usage\UsageJob;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Usage\Types;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class UsageOnOrderCreate
{
    use DispatchesJobs,SerializesModels;

    /**
     * Handle the event.
     *
     * @param OrderTransactionCompleted $event
     * @return void
     */
    public function handle(OrderTransactionCompleted $event)
    {
        $usage_type = $event->getOrder()->sales_channel_id == SalesChannelIds::WEBSTORE ? Types::PRODUCT_LINK : Types::POS_ORDER_CREATE;
        $this->dispatch((new UsageJob((int) $event->getOrder()->partner_id, $usage_type)));
    }
}
