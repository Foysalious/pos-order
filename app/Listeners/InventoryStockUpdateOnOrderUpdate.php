<?php namespace App\Listeners;

use App\Events\OrderUpdated;
use App\Jobs\Order\StockUpdate\InventoryStockUpdateForOrderUpdate;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class InventoryStockUpdateOnOrderUpdate
{
    use DispatchesJobs,SerializesModels;

    public function handle(OrderUpdated $event)
    {
        if(!empty($event->getStockUpdateData())) {
            $this->dispatch(new InventoryStockUpdateForOrderUpdate($event->getStockUpdateData()));
        }
    }
}
