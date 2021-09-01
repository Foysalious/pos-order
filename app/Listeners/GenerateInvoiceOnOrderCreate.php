<?php namespace App\Listeners;

use App\Events\OrderTransactionCompleted;
use App\Jobs\Order\OrderInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\SerializesModels;

class GenerateInvoiceOnOrderCreate extends Job implements ShouldQueue
{
    protected $model;

    use DispatchesJobs, SerializesModels;


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

    public function getJobId()
    {
        // TODO: Implement getJobId() method.
    }

    public function getRawBody()
    {
        // TODO: Implement getRawBody() method.
    }
}
