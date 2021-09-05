<?php namespace App\Listeners;

use App\Events\OrderPlaceTransactionCompleted;
use App\Jobs\Order\OrderInvoice;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerateInvoiceOnOrderCreate
{
    use DispatchesJobs;


    /**
     * Handle the event.
     *
     * @param OrderPlaceTransactionCompleted $event
     * @return void
     */

    public function handle(OrderPlaceTransactionCompleted $event)
    {
        $this->dispatch((new OrderInvoice($event->getOrder())));
    }

}
