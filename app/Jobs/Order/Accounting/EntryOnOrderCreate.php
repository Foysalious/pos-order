<?php namespace App\Jobs\Order\Accounting;

use App\Jobs\Job;
use App\Models\Order;
use App\Services\Accounting\CreateEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderCreate extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;
    protected int $tries = 3;

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

    public function handle(CreateEntry $createEntry)
    {
        if ($this->attempts() > 2) return;
        $createEntry->setOrder($this->order)->create();
    }
}
