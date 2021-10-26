<?php namespace App\Services\Order\Notification;


use App\Models\Order;
use App\Services\Order\PriceCalculation;
use App\Services\PushNotification\PushNotificationHandler;

class OrderPlacePushNotificationHandler
{
    private Order $order;

    /**
     * @param Order $order
     * @return OrderPlacePushNotificationHandler
     */
    public function setOrder(Order $order): OrderPlacePushNotificationHandler
    {
        $this->order = $order;
        return $this;
    }

    public function handle()
    {
        $topic = config('notification.push_notification_topic_name.manager') . $this->order->partner_id;
        $channel = config('notification.push_notification_channel_name.manager');
        $sound = config('notification.push_notification_sound.manager');
        /** @var PriceCalculation $priceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        $priceCalculation = $priceCalculation->setOrder($this->order);
        $discountedPrice = $priceCalculation->getDiscountedPrice();
        $payment_status = $priceCalculation->getPaid() ? 'প্রদত্ত' : 'বকেয়া';
        $order_id = $this->order->id;
        $partner_wise_order_id = $this->order->partner_wise_order_id;
        $sales_channel = 'অনলাইন স্টোর';
        $notification_data = [
            "title" => 'New Online Store Order',
            "message" => "অর্ডার # $partner_wise_order_id: নতুন অর্ডার দেওয়া হয়েছে। মোট টাকার পরিমাণ: $discountedPrice ($payment_status)\r\n চ্যানেল: $sales_channel",
            "sound" => $sound,
            "event_type" => 'WebstoreOrder',
            "event_id" => (string)$order_id,
            "channelId" => $channel
        ];
        (new PushNotificationHandler())->send($topic, null, $notification_data, 'high');
    }
}
