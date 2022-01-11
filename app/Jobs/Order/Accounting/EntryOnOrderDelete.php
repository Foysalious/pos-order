<?php namespace App\Jobs\Order\Accounting;

use App\Jobs\Job;
use App\Models\Order;
use App\Services\Accounting\DeleteEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderDelete  extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->connection = 'pos_order_accounting_queue';
        $this->queue = 'pos_order_accounting_queue';
        $this->order = $order;
    }

    public function handle(DeleteEntry $deleteEntry)
    {
        $deleteEntry->setOrder($this->order)->delete();
    }
}
