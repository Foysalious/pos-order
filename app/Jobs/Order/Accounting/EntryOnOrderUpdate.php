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
    private array $previousOrderData;

    /**
     * Create a new job instance.
     * @param Order $order
     * @param array $order_product_change_data
     */
    public function __construct(Order $order, array $order_product_change_data, private EventNotification $eventNotification, array $previousOrderData)
    {
        $this->connection = 'pos_order_accounting_queue';
        $this->queue = 'pos_order_accounting_queue';
        $this->order = $order;
        $this->orderProductChangeData = $order_product_change_data;
        $this->previousOrderData = $previousOrderData;
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
            ->setPreviousOrderData($this->previousOrderData)
            ->update();
    }
}
