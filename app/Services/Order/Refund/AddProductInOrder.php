<?php


namespace App\Services\Order\Refund;

use App\Services\OrderSku\Creator;
use Illuminate\Support\Facades\App;

class AddProductInOrder extends ProductOrder
{
    public function update()
    {
        $this->addItemsInOrderSku();
    }

    private function getAddedItems()
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = $this->skus->pluck('id');
        $items = $request_products->diff($current_products);
        return $this->skus->whereIn('id',$items);
    }

    private function addItemsInOrderSku()
    {
        $items = array_values($this->getAddedItems()->toArray());
        /** @var Creator $creator */
        $creator = App::make(Creator::class);
        $creator->setOrder($this->order)->setSkus($items)->create();



    }
}
