<?php namespace App\Listeners\Accounting;

use App\Events\OrderUpdated;
use App\Jobs\Order\Accounting\EntryOnOrderUpdate;
use App\Services\EventNotification\Events;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderUpdating
{
    use DispatchesJobs, SerializesModels, AccountingEventNotification;


    /**
     * Handle the event.
     *
     * @param OrderUpdated $event
     * @return void
     */
    public function handle(OrderUpdated $event)
    {
        if (empty($event->getOrderProductChangedData())) return;
        $this->dispatch(new EntryOnOrderUpdate($event->getOrder(), $event->getOrderProductChangedData(), $this->createEventNotification($event->getOrder(), Events::ORDER_UPDATE),
            $event->getPreviousOrder(), $event->getPaymentInfo()));
    }
}
