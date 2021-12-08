<?php namespace App\Listeners;

use App\Events\OrderPlaceTransactionCompleted;
use App\Services\Product\StockManager;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class InventoryStockUpdateOnOrderPlace
{
    use DispatchesJobs,SerializesModels;

    public function handle(OrderPlaceTransactionCompleted $event)
    {
        $order = $event->getOrder();

        /** @var StockManager $stock_manager */
        $stock_manager = app(StockManager::class);
        $order->orderSkus->each(function ($order_sku) use (&$data, $stock_manager){
            if(!is_null($order_sku->sku_id)) {
                $stock_manager->setSkuId($order_sku->sku_id)->decreaseAndInsertInChunk($order_sku->quantity);
            }
        });
        $stock_manager->updateStock();
    }
}
