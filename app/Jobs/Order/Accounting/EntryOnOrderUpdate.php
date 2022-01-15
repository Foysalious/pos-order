<?php namespace App\Jobs\Order\Accounting;

use App\Jobs\Job;
use App\Models\EventNotification;
use App\Models\Order;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\Accounting\UpdateEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderUpdate extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;
    private array $orderProductChangeData;

    /**
     * Create a new job instance.
     * @param Order $order
     * @param array $order_product_change_data
     */
    public function __construct(Order $order, array $order_product_change_data, private EventNotification $eventNotification)
    {
        $this->connection = 'pos_order_accounting_queue';
        $this->queue = 'pos_order_accounting_queue';
        $this->order = $order;
        $this->orderProductChangeData = $order_product_change_data;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function handle(UpdateEntry $updateEntry)
    {
        $updateEntry
            ->setOrder($this->order)
            ->setEventNotification($this->eventNotification)
            ->setOrderProductChangeData($this->orderProductChangeData)
            ->update();
    }
}
