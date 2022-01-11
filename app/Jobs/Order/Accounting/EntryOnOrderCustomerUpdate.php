<?php namespace App\Jobs\Order\Accounting;

use App\Jobs\Job;
use App\Models\Order;
use App\Services\Accounting\CustomerUpdateEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderCustomerUpdate extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;

    /**
     * Create a new job instance.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->connection = 'pos_order_accounting_queue';
        $this->queue = 'pos_order_accounting_queue';
        $this->order = $order;
    }

    public function handle(CustomerUpdateEntry $customerUpdateEntry)
    {
        $customerUpdateEntry->setOrder($this->order)->customerUpdateEntry();
    }
}
