<?php

namespace App\Listeners;

use App\Events\OrderPlaceTransactionCompleted;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\OrderSms\WebstoreOrderSms;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class WebstoreSmsSendForOrder
{
    use DispatchesJobs,SerializesModels;


    public function handle(OrderPlaceTransactionCompleted $event)
    {
        $order = $event->getOrder();
        if($order->sales_channel_id == SalesChannelIds::WEBSTORE) {
            dispatch(new WebstoreOrderSms($order->partner_id, $order->id));
        }
    }
}
