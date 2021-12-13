<?php namespace App\Jobs\Order\Accounting;

use App\Models\Order;
use App\Services\Accounting\CustomerUpdateEntry;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderCustomerUpdate
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;
    protected int $tries = 1;

    /**
     * Create a new job instance.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(CustomerUpdateEntry $customerUpdateEntry)
    {
        if ($this->attempts() > 2) return;
        $customerUpdateEntry->setOrder($this->order)->customerUpdateEntry();
    }
}
