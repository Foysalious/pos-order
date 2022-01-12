<?php namespace App\Jobs\Order\Accounting;

use App\Jobs\Job;
use App\Models\EventNotification;
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
     * @param EventNotification $eventNotification
     */
    public function __construct(Order $order, private EventNotification $eventNotification)
    {
        $this->connection = 'pos_order_accounting_queue';
        $this->queue = 'pos_order_accounting_queue';
        $this->order = $order;
    }

    public function handle(CustomerUpdateEntry $customerUpdateEntry)
    {
        $customerUpdateEntry->setOrder($this->order)->setEventNotification($this->eventNotification)->customerUpdateEntry();
    }
}
