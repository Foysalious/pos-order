<?php namespace App\Listeners\Accounting;

use App\Events\OrderPlaceTransactionCompleted;
use App\Jobs\Order\Accounting\EntryOnOrderCreate;
use App\Services\EventNotification\Events;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderCreation
{
    use DispatchesJobs, SerializesModels, AccountingEventNotification;

    /**
     * Handle the event.
     *
     * @param OrderPlaceTransactionCompleted $event
     * @return void
     */
    public function handle(OrderPlaceTransactionCompleted $event)
    {
        $event_notification = $this->createEventNotification($event->getOrder(), Events::ORDER_CREATE);
        $this->dispatch(new EntryOnOrderCreate($event->getOrder(), $event_notification));
    }
}
