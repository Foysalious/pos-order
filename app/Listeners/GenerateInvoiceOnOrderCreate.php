<?php namespace App\Listeners;

use App\Events\OrderCreated;
use App\Http\Reports\InvoiceService;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class GenerateInvoiceOnOrderCreate
{
    use DispatchesJobs, SerializesModels;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Handle the event.
     *
     * @param OrderCreated $event
     * @return void
     */

    public function handle(OrderCreated $event)
    {
        $this->invoiceService->setOrder($event->getOrder()->id)->generateInvoice();
    }

}
