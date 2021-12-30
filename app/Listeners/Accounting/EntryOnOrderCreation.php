<?php namespace App\Listeners\Accounting;

use App\Events\OrderPlaceTransactionCompleted;
use App\Jobs\Order\Accounting\EntryOnOrderCreate;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderCreation
{
    use DispatchesJobs,SerializesModels;

    /**
     * Handle the event.
     *
     * @param  OrderPlaceTransactionCompleted  $event
     * @return void
     */
    public function handle(OrderPlaceTransactionCompleted $event)
    {
        $this->dispatch(new EntryOnOrderCreate($event->getOrder()));
    }
}
