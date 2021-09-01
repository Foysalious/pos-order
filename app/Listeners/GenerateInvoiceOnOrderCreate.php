<?php namespace App\Listeners;

use App\Events\OrderTransactionCompleted;
use App\Jobs\Order\OrderInvoice;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerateInvoiceOnOrderCreate
{
    use DispatchesJobs;


    /**
     * Handle the event.
     *
     * @param OrderTransactionCompleted $event
     * @return void
     */

    public function handle(OrderTransactionCompleted $event)
    {
        $this->dispatch((new OrderInvoice($event->getOrder())));
    }

}
