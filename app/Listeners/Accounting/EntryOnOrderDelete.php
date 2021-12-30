<?php namespace App\Listeners\Accounting;

use App\Events\OrderDeleted;
use App\Jobs\Order\Accounting\EntryOnOrderDelete as OrderDeleteEntryJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderDelete
{
    use DispatchesJobs,SerializesModels;


    /**
     * Handle the event.
     *
     * @param OrderDeleted $event
     * @return void
     */
    public function handle(OrderDeleted $event)
    {
        $this->dispatch(new OrderDeleteEntryJob($event->getOrder()));
    }
}
