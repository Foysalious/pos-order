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
    protected int $tries = 1;

    public function __construct(Order $order)
    {
        $this->connection = 'accounting_queue';
        $this->queue = 'accounting_queue';
        $this->order = $order;
    }

    public function handle(DeleteEntry $deleteEntry)
    {
        if ($this->attempts() > 2) return;
        $deleteEntry->setOrder($this->order)->delete();
    }
}
