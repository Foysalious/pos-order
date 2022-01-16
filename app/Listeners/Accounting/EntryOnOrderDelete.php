<?php namespace App\Listeners\Accounting;

use App\Events\OrderDeleted;
use App\Jobs\Order\Accounting\EntryOnOrderDelete as OrderDeleteEntryJob;
use App\Services\EventNotification\Events;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderDelete
{
    use DispatchesJobs, SerializesModels, AccountingEventNotification;


    /**
     * Handle the event.
     *
     * @param OrderDeleted $event
     * @return void
     */
    public function handle(OrderDeleted $event)
    {
        $event_notification = $this->createEventNotification($event->getOrder(), Events::ORDER_DELETE);
        $this->dispatch(new OrderDeleteEntryJob($event->getOrder(), $event_notification));
    }
}
