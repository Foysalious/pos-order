<?php namespace App\Services\Order;

use App\Models\Order;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Refund\Objects\AddRefundTracker;
use App\Services\Product\StockManageByChunk;
use Illuminate\Support\Facades\App;

class StockRefillerForFailedUpdate
{
    protected Order $order;
    protected array $orderProductChangeData;

    public function __construct(
        protected StockManageByChunk $stockManageByChunk,
        protected InventoryServerClient $client
    )
    {
    }


    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param array $orderProductChangeData
     * @return StockRefillerForFailedUpdate
     */
    public function setOrderProductChangeData(array $orderProductChangeData): StockRefillerForFailedUpdate
    {
        $this->orderProductChangeData = $orderProductChangeData;
        return $this;
    }

    public function refillStock()
    {
        $order_skus = $this->order->orderSkus()->withTrashed()->get();
        $sku_ids = $order_skus->whereNotNull('sku_id')->pluck('sku_id');
        $sku_details = $this->getSkuDetails($sku_ids)->keyBy('id')->toArray();
        $this->stockManageByChunk->setOrder($this->order);
        if (isset($this->orderProductChangeData['deleted']['refunded_products'])){
            foreach ($this->orderProductChangeData['deleted']['refunded_products'] as $refunded){
                if($refunded['sku_id'] != null){
                    $this->stockManageByChunk->setSku($sku_details[$refunded['sku_id']]);
                    if($this->stockManageByChunk->isStockMaintainable()){
                        $this->stockManageByChunk->decreaseAndInsertInChunk($refunded['quantity']);
                    }
                }
            }
        }

        if (isset($this->orderProductChangeData['new'])){
            foreach ($this->orderProductChangeData['new'] as $each_sku){
                if($each_sku->sku_id != null){
                    $this->stockManageByChunk->setSku($sku_details[$each_sku->sku_id]);
                    if($this->stockManageByChunk->isStockMaintainable()){
                        $this->stockManageByChunk->increaseAndInsertInChunk($each_sku->quantity);
                    }
                }
            }
        }

        if (isset($this->orderProductChangeData['refund_exchanged']['added_products'])){
            /** @var AddRefundTracker $added */
            foreach ($this->orderProductChangeData['refund_exchanged']['added_products'] as $added){
                if($added->getSkuId() != null){
                    $this->stockManageByChunk->setSku($sku_details[$added->getSkuId()]);
                    if($this->stockManageByChunk->isStockMaintainable()){
                        $this->stockManageByChunk->increaseAndInsertInChunk($added->getQuantityChangedValue());
                    }
                }
            }
        }

        if (isset($this->orderProductChangeData['refund_exchanged']['refunded_products'])){
            /** @var AddRefundTracker $added */
            foreach ($this->orderProductChangeData['refund_exchanged']['refunded_products'] as $refunded){
                if($refunded->getSkuId() != null){
                    $this->stockManageByChunk->setSku($sku_details[$refunded->getSkuId()]);
                    if($this->stockManageByChunk->isStockMaintainable()){
                        $this->stockManageByChunk->decreaseAndInsertInChunk($refunded->getQuantityChangedValue());
                    }
                }
            }
        }

        $this->stockManageByChunk->updateStock();
    }


    private function getSkuDetails($sku_ids)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids->toArray()) .'&channel_id=1';
        $sku_details = $this->client->get($url)['skus'] ?? [];
        return collect($sku_details);
    }

}
