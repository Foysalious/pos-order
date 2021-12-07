<?php namespace App\Listeners;

use App\Events\OrderPlaceTransactionCompleted;
use App\Events\OrderUpdated;
use App\Services\Product\StockManageByChunk;
use App\Services\Product\StockManager;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class InventoryStockUpdater
{
    use DispatchesJobs,SerializesModels;

    public function handle(OrderPlaceTransactionCompleted | OrderUpdated $event)
    {
        $stock_update_data = $event->getStockUpdateData();

        if(!empty($stock_update_data)) {
            /** @var StockManageByChunk $stock_manager */
            $stock_manager = app(StockManageByChunk::class);
            foreach ($stock_update_data as $item) {
                if ($item['operation'] == StockManager::STOCK_INCREMENT)
                    $stock_manager->setSku($item['sku_detail'])->increaseAndInsertInChunk($item['quantity']);
                if ($item['operation'] == StockManager::STOCK_DECREMENT)
                    $stock_manager->setSku($item['sku_detail'])->decreaseAndInsertInChunk($item['quantity']);
            }
            $stock_manager->updateStock();
        }
    }
}
