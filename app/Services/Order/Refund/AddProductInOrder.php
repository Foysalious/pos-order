<?php


namespace App\Services\Order\Refund;


use App\Services\Order\Creator;
use Illuminate\Support\Facades\App;

class AddProductInOrder extends ProductOrder
{
    public function update()
    {
        $this->addItemsInOrderSku();
    }

    private function getAddedItems()
    {
        $current_products = $this->order->items()->pluck('sku_id');
        $request_products = $this->skus->pluck('id');
        $items = $request_products->diff($current_products);
        return $this->skus->whereIn('id',$items);
    }

    private function addItemsInOrderSku()
    {
        $items = $this->getAddedItems();
        /** @var Creator $creator */
        $creator = App::make(Creator::class);
        $creator->setPartner($this->order->partner_id);


    }
}
