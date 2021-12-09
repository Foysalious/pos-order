<?php namespace App\Listeners;

use App\Events\OrderUpdated;
use App\Services\Product\StockManager;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class InventoryStockUpdateOnOrderUpdate
{
    use DispatchesJobs,SerializesModels;

    public function handle(OrderUpdated $event)
    {
        $stock_update_data = $event->getStockUpdateData();
        if(!empty($stock_update_data)) {
            /** @var StockManager $stock_manager */
            $stock_manager = app(StockManager::class);
            foreach ($stock_update_data as $item) {
                if ($item['operation'] == StockManager::STOCK_INCREMENT)
                    $stock_manager->setSkuId($item['sku_detail']['id'])->increaseAndInsertInChunk($item['quantity']);
                if ($item['operation'] == StockManager::STOCK_DECREMENT)
                    $stock_manager->setSkuId($item['sku_detail']['id'])->decreaseAndInsertInChunk($item['quantity']);
            }
            $stock_manager->updateStock();
        }
    }
}
