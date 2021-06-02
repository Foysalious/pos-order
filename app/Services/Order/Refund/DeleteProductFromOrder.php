<?php


namespace App\Services\Order\Refund;


class DeleteProductFromOrder extends ProductOrder
{

    public function update()
    {
        return $this->deleteItemsFromOrderSku();
    }

    private function deleteItemsFromOrderSku()
    {
        $items = array_values($this->getDeletedItems()->toArray());
        return $this->order->orderSkus()->whereIn('id', $items)->delete();

    }

    private function getDeletedItems()
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = $this->skus->pluck('id');
        return $current_products->diff($request_products);
    }
}
