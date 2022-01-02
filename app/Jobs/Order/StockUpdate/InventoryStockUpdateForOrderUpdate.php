<?php namespace App\Jobs\Order\StockUpdate;

use App\Jobs\Job;
use App\Services\Product\StockManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InventoryStockUpdateForOrderUpdate extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private array $stockUpdateData;
    protected int $tries = 1;
    private int $partnerId;

    /**
     * Create a new job instance.
     * @param array $stock_update_data
     */
    public function __construct(array $stock_update_data, int $partnerId)
    {
        $this->stockUpdateData = $stock_update_data;
        $this->partnerId = $partnerId;
    }

    public function handle(StockManager $stock_manager)
    {
        if(!empty($this->stockUpdateData)) {
            foreach ($this->stockUpdateData as $item) {
                if ($item['operation'] == StockManager::STOCK_INCREMENT)
                    $stock_manager->setSkuId($item['sku_detail']['id'])->increaseAndInsertInChunk($item['quantity']);
                if ($item['operation'] == StockManager::STOCK_DECREMENT)
                    $stock_manager->setSkuId($item['sku_detail']['id'])->decreaseAndInsertInChunk($item['quantity']);
            }
            $stock_manager->setPartnerId($this->partnerId)->updateStock();
        }

    }
}
