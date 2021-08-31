<?php


namespace App\Services\Order;


use App\Models\Order;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Product\StockManageByChunk;
use Illuminate\Support\Facades\App;

class StockRefillerForCanceledOrder
{
    const STOCK_INCREMENT = 'increment';
    protected Order $order;

    public function __construct(
        protected StockManageByChunk $stockManageByChunk
    )
    {
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function refillStock()
    {
        $data = $this->makeData();
        dd($data);
        $this->stockManageByChunk->setOrder($this->order)->manageStock($data);

    }

    public function makeData()
    {
        $data = [];
        $order_skus = $this->order->orderSkus()->get();
        $sku_ids = $order_skus->whereNotNull('sku_id')->pluck('sku_id');
        $sku_details = $this->getSkuDetails($sku_ids)->keyBy('id')->toArray();
        foreach ($order_skus as $each_sku) {
            if($each_sku->sku_id != null) {
                if($this->isStockMaintainable($sku_details[$each_sku->sku_id])) {
                    $data [] = [
                        'id' => $each_sku->sku_id,
                        'product_id' => $sku_details[$each_sku->sku_id]['product_id'],
                        'operation' => self::STOCK_INCREMENT,
                        'quantity' => (float) $each_sku->quantity
                    ];
                }
            }
        }
        return $data;
    }


    private function getSkuDetails($sku_ids)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids->toArray()) .'&channel_id=1';
        /** @var InventoryServerClient $client */
        $client = App::make(InventoryServerClient::class);
        $sku_details = $client->setBaseUrl()->get($url)['skus'] ?? [];
        return collect($sku_details);
    }

    private function isStockMaintainable($sku)
    {
        if(is_null($sku['stock'])) {
            return false;
        } else {
            return true;
        }
    }

}
