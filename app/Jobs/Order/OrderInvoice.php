<?php

namespace App\Jobs\Order;

use App\Http\Reports\InvoiceService;
use App\Jobs\Job;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderInvoice extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private Order $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->attempts() > 2) return;
        /** @var InvoiceService $invoice_service */
        $invoice_service = app(InvoiceService::class);
        $invoice_service->setOrder($this->order)->generateInvoice();
    }
}
