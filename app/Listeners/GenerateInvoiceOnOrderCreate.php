<?php namespace App\Listeners;

use App\Events\OrderCustomerUpdated;
use App\Events\OrderPlaceTransactionCompleted;
use App\Events\OrderUpdated;
use App\Jobs\Order\OrderInvoice;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerateInvoiceOnOrderCreate
{
    use DispatchesJobs;


    /**
     * Handle the event.
     *
     * @param OrderPlaceTransactionCompleted|OrderUpdated|OrderCustomerUpdated $event
     * @return void
     */

    public function handle(OrderPlaceTransactionCompleted|OrderUpdated|OrderCustomerUpdated $event)
    {
        $this->dispatch((new OrderInvoice($event->getOrder())));
    }

}
