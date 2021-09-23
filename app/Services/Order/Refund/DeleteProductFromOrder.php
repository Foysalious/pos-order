<?php namespace App\Services\Order\Refund;

use App\Services\Order\Constants\PaymentMethods;
use App\Services\Transaction\Constants\TransactionTypes;
use Illuminate\Support\Collection;

class DeleteProductFromOrder extends ProductOrder
{
    private float $refunded_amount = 0;

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
        $this->calculateRefundAmountOfDeletedProducts($deleted_skus_ids,$order_skus_details);
        return [
            'refunded_amount' => $this->refunded_amount,
            'refunded_products' => $order_skus_details->map->only(['id','order_id','sku_id','quantity','unit_price'])->all(),
        ];
    }

    private function getDeletedItems()
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = $this->skus->pluck('id');
        return $current_products->diff($request_products);
    }

    /**
     * @param Collection $skus
     */
    private function stockRefillForDeletedItems(Collection $skus)
    {
        $sku_ids = $skus->where('sku_id', '<>', null)->pluck('sku_id')->toArray();
        $skus_inventory_details = $sku_ids ? collect($this->getSkuDetails($sku_ids, $this->order->sales_channel_id))->keyBy('id')->toArray() : [];
        foreach ($skus as $sku) {
            if ($sku->id == null) continue;
            if(isset($skus_inventory_details[$sku->sku_id])) {
                $this->stockManager->setSku($skus_inventory_details[$sku->sku_id])->setOrder($this->order)->increase($sku->quantity);
            }
        }
    }

    private function calculateRefundAmountOfDeletedProducts(array $deleted_skus_ids, Collection $order_skus_details)
    {
        $total_refund = 0;
        foreach ($order_skus_details as $each) {
            if(in_array($each->id,$deleted_skus_ids)) $total_refund = $total_refund + ($each->unit_price * $each->quantity);
        }
        $this->refunded_amount = $total_refund;
    }
}
