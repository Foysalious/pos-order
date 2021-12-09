<?php namespace App\Services\Order;

use App\Models\Order;
use App\Services\Product\StockManager;

class StockRefillerForCanceledOrder
{
    protected Order $order;

    public function __construct(
        protected StockManager $stockManager
    )
    {
    }

    /**
     * @param Order $order
     * @return StockRefillerForCanceledOrder
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function refillStock()
    {
        $order_skus = $this->order->orderSkus()->get();
        $this->stockManager->setOrder($this->order);
        foreach ($order_skus as $each_sku) {
            if($each_sku->sku_id != null) {
                $this->stockManager->setSkuId($each_sku->sku_id)->increaseAndInsertInChunk($each_sku->quantity);
            }
        }
        $this->stockManager->updateStock();
    }

}
