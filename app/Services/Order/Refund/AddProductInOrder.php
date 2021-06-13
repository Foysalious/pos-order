<?php namespace App\Services\Order\Refund;

use App\Services\OrderSku\Creator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class AddProductInOrder extends ProductOrder
{
    public function update()
    {
        return $this->addItemsInOrderSku();
    }

    private function addItemsInOrderSku()
    {
        $all_items = $this->getAddedItems();
        $null_sku_items = $all_items->where('id','=', null);
        $sku_items = $all_items->where('id','<>', null)->toArray();
        if($sku_items) {
            /** @var Creator $creator */
            $creator = App::make(Creator::class);
            $creator->setOrder($this->order)->setSkus($sku_items)->create();
        }
        if ($null_sku_items) {
            $this->addNullSkuItems($null_sku_items);
        }

        return true;
    }

    private function getAddedItems()
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = $this->skus->pluck('id');
        $items = $request_products->diff($current_products);
        return $this->skus->whereIn('id',$items);
    }

    private function addNullSkuItems(Collection $items)
    {
        foreach ($items as $item) {
            $data['sku_id'] = null;
            $data['quantity'] = $item->quantity;
            $data['unit_price'] = $item->price;
            $data['order_id'] = $this->order->id;
            $this->orderSkuRepository->create($data);
        }
    }

}
