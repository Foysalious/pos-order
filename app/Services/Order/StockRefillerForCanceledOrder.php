<?php


namespace App\Services\Order;


use App\Models\Order;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Product\StockManageByChunk;
use Illuminate\Support\Facades\App;

class StockRefillerForCanceledOrder
{
    protected Order $order;

    public function __construct(
        protected StockManageByChunk $stockManageByChunk
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
        $sku_ids = $order_skus->whereNotNull('sku_id')->pluck('sku_id');
        $sku_details = $this->getSkuDetails($sku_ids)->keyBy('id')->toArray();
        $this->stockManageByChunk->setOrder($this->order);
        foreach ($order_skus as $each_sku) {
            if($each_sku->sku_id != null) {
                $this->stockManageByChunk->setSku($sku_details[$each_sku->sku_id]);
                if($this->stockManageByChunk->isStockMaintainable()){
                    $this->stockManageByChunk->increaseAndInsertInChunk($each_sku->quantity);
                }
            }
        }
        $this->stockManageByChunk->updateStock();
    }


    private function getSkuDetails($sku_ids)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids->toArray()) .'&channel_id=1';
        /** @var InventoryServerClient $client */
        $client = App::make(InventoryServerClient::class);
        $sku_details = $client->setBaseUrl()->get($url)['skus'] ?? [];
        return collect($sku_details);
    }

}
