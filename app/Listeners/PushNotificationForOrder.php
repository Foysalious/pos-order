<?php

namespace App\Listeners;

use App\Events\OrderPlaceTransactionCompleted;
use App\Jobs\Order\OrderPlacePushNotification;
use App\Services\Order\Constants\SalesChannelIds;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class PushNotificationForOrder
{
    use DispatchesJobs,SerializesModels;


    public function handle(OrderPlaceTransactionCompleted $event)
    {
        $order = $event->getOrder();
        if($order->sales_channel_id == SalesChannelIds::WEBSTORE) {
            $this->dispatch(new OrderPlacePushNotification($order));
        }
    }
}
