<?php namespace App\Listeners;

use App\Events\OrderPlaceTransactionCompleted;
use App\Jobs\Order\StockUpdate\InventoryStockUpdateOnOrderCreate;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class InventoryStockUpdateOnOrderPlace
{
    use DispatchesJobs,SerializesModels;

    public function handle(OrderPlaceTransactionCompleted $event)
    {
        $this->dispatch(new InventoryStockUpdateOnOrderCreate($event->getOrder()));
    }
}
