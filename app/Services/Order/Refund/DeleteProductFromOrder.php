<?php namespace App\Services\Order\Refund;

use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Services\Product\StockManager;
use Illuminate\Support\Collection;

class DeleteProductFromOrder extends ProductOrder
{
    private float $refunded_amount = 0;
    private array $stockUpdateData = [];


    public function update(): array
    {
        return $this->deleteItemsFromOrderSku();
    }

    /**
     * @return array
     */
    private function deleteItemsFromOrderSku() : array
    {
        $deleted_skus_ids = array_values($this->getDeletedItems()->toArray());
        $order_skus_details = clone $this->order->orderSkus()->whereIn('id', $deleted_skus_ids)->get();
        $deleted = $this->order->orderSkus()->whereIn('id', $deleted_skus_ids)->delete();
        if($deleted) {
            $this->stockUpdateData = $this->makeStockUpdateData($order_skus_details);
        }
        $this->calculateRefundAmountOfDeletedProducts($deleted_skus_ids,$order_skus_details);
        return [
            'refunded_amount' => $this->refunded_amount,
            'refunded_products' => $order_skus_details->map->only(['id','order_id','sku_id','quantity','unit_price'])->all(),
        ];
    }

    private function getDeletedItems()
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = $this->skus->pluck('order_sku_id');
        return $current_products->diff($request_products);
    }

    /**
     * @param Collection $skus
     * @return array
     */
    private function makeStockUpdateData(Collection $skus): array
    {
        $sku_ids = $skus->where('sku_id', '<>', null)->pluck('sku_id')->toArray();
        $skus_inventory_details = $sku_ids ? collect($this->getSkuDetails($sku_ids, $this->order->sales_channel_id))->keyBy('id')->toArray() : [];
        $stock_update_data = [];
        foreach ($skus as $sku) {
            if ($sku->id == null) continue;
            if(isset($skus_inventory_details[$sku->sku_id])) {
                $stock_update_data [] = [
                    'sku_detail' => $skus_inventory_details[$sku->sku_id],
                    'quantity' => (float) $sku->quantity,
                    'operation' => StockManager::STOCK_INCREMENT
                ];
            }
        }
        return $stock_update_data;
    }

    private function calculateRefundAmountOfDeletedProducts(array $deleted_skus_ids, Collection $order_skus_details)
    {
        $total_refund = 0;
        foreach ($order_skus_details as $each) {
            if(in_array($each->id,$deleted_skus_ids)) $total_refund = $total_refund + ($each->unit_price * $each->quantity);
        }
        $this->refunded_amount = $total_refund;
    }

    /**
     * @return array
     */
    public function getStockUpdateData(): array
    {
        return $this->stockUpdateData;
    }
}
