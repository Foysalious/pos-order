<?php namespace App\Jobs\Order\Accounting;

use App\Jobs\Job;
use App\Models\Order;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\Accounting\OrderDueEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderDueCleared  extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;
    private float $paidAmount;
    protected int $tries = 3;

    public function __construct(Order $order, float $paidAmount)
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
        if ($this->attempts() > 2) return;
        $dueEntry->setOrder($this->order)->setPaidAmount($this->paidAmount)->create();
    }
}
