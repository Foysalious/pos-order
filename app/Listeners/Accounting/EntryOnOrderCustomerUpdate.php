<?php namespace App\Listeners\Accounting;

use App\Events\OrderCustomerUpdated;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Jobs\Order\Accounting\EntryOnOrderCustomerUpdate as CustomerUpdateEntryJob;
use App\Services\EventNotification\Events;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderCustomerUpdate
{
    use DispatchesJobs, SerializesModels, AccountingEventNotification;

    /**
     * Handle the event.
     *
     * @param OrderCustomerUpdated $event
     * @return void
     */
    public function handle(OrderCustomerUpdated $event)
    {
        $event_notification = $this->createEventNotification($event->getOrder(), Events::ORDER_UPDATE);
        $this->dispatch(new CustomerUpdateEntryJob($event->getOrder(), $event_notification));
    }
}
