<?php namespace App\Listeners\Accounting;

use App\Events\OrderUpdated;
use App\Jobs\Order\Accounting\EntryOnOrderUpdate;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderUpdating
{
    use DispatchesJobs,SerializesModels;


    /**
     * Handle the event.
     *
     * @param OrderUpdated $event
     * @return void
     */
    public function handle(OrderUpdated $event)
    {
        if (!empty($event->getOrderProductChangedData())) {
            $this->dispatch(new EntryOnOrderUpdate($event->getOrder(),$event->getOrderProductChangedData()));
        }
    }
}
