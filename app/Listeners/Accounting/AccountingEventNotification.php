<?php namespace App\Listeners\Accounting;

use App\Models\EventNotification;
use App\Models\Order;
use App\Services\EventNotification\Services;

trait AccountingEventNotification
{
    private function createEventNotification(Order $order, string $eventName): EventNotification
    {
        $event_notification = new EventNotification();
        $event_notification->order_id = $order->id;
        $event_notification->event = $eventName;
        $event_notification->service = Services::ACCOUNTING;
        $event_notification->save();
        return $event_notification;
    }
}
