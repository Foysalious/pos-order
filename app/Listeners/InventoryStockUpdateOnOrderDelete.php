<?php namespace App\Listeners;

use App\Events\OrderDeleted;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Order\StockUpdate\InventoryStockUpdateOnOrderDelete as StockUpdateJob;

class InventoryStockUpdateOnOrderDelete
{
    use DispatchesJobs,SerializesModels;

    public function handle(OrderDeleted $event)
    {
        $this->dispatch(new StockUpdateJob($event->getOrder()));
    }
}
