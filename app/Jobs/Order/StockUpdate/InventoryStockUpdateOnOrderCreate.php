<?php namespace App\Jobs\Order\StockUpdate;

use App\Jobs\Job;
use App\Models\Order;
use App\Services\Product\StockManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InventoryStockUpdateOnOrderCreate extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;
    protected int $tries = 1;

    /**
     * Create a new job instance.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(StockManager $stock_manager)
    {
        if ($this->attempts() > 2) return;
        $this->order->orderSkus->each(function ($order_sku) use (&$data, $stock_manager){
            if(!is_null($order_sku->sku_id)) {
                $stock_manager->setSkuId($order_sku->sku_id)->decreaseAndInsertInChunk($order_sku->quantity);
            }
        });
        $stock_manager->updateStock();

    }
}
