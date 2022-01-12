<?php namespace App\Jobs\Order\Accounting;

use App\Jobs\Job;
use App\Models\EventNotification;
use App\Models\Order;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\Accounting\OrderDueEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderDueCleared extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;
    private float $paidAmount;

    public function __construct(Order $order, float $paidAmount, private EventNotification $eventNotification)
    {
        $this->connection = 'pos_order_accounting_queue';
        $this->queue = 'pos_order_accounting_queue';
        $this->order = $order;
        $this->paidAmount = $paidAmount;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function handle(OrderDueEntry $dueEntry)
    {
        $dueEntry->setOrder($this->order)->setEventNotification($this->eventNotification)->setPaidAmount($this->paidAmount)->create();
    }
}
