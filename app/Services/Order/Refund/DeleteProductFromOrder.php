<?php namespace App\Services\Order\Refund;

use Illuminate\Support\Collection;

class DeleteProductFromOrder extends ProductOrder
{

    public function update()
    {
        return $this->deleteItemsFromOrderSku();
    }

    private function deleteItemsFromOrderSku()
    {
        $deleted_skus_ids = array_values($this->getDeletedItems()->toArray());
        $order_skus_details = $this->order->orderSkus()->whereIn('id', $deleted_skus_ids)->get();
        $deleted = $this->order->orderSkus()->whereIn('id', $deleted_skus_ids)->delete();
        if($deleted) $this->stockRefillForDeletedItems($order_skus_details);
    }

    private function getDeletedItems()
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = $this->skus->pluck('id');
        return $current_products->diff($request_products);
    }

    /**
     * @param Collection $order_skus_details
     */
    private function stockRefillForDeletedItems(Collection $skus)
    {
        $sku_ids = $skus->where('sku_id', '<>', null)->pluck('sku_id')->toArray();
        $skus_inventory_details = collect($this->getSkuDetails($sku_ids, $this->order->sales_channel_id))->keyBy('id')->toArray();
        foreach ($skus as $sku) {
            if ($sku->id == null) continue;
            if(isset($skus_inventory_details[$sku->sku_id])) {
                $this->stockManager->setSku($skus_inventory_details[$sku->sku_id])->setOrder($this->order)->increase($sku->quantity);
            }
        }
    }
}
