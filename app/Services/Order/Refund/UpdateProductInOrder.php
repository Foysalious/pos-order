<?php


namespace App\Services\Order\Refund;


use App\Services\OrderSku\Creator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class UpdateProductInOrder extends ProductOrder
{
    const QUANTITY_INCREASED = 'increment';
    const QUANTITY_DECREASED = 'decrement';

    public function update()
    {
        $updated_products = $this->getUpdatedProducts();
        $skus_details = $this->getUpdatedProductsSkuDetails($updated_products); //only updated product's sku details

        foreach ($updated_products as $each) {
            if($each['sku_id'] == null)
                $this->handleNullOrderSkuItem($each);
            elseif (array_key_exists('quantity', $each) && !array_key_exists('price', $each)){
                $this->handleQuantityUpdateForOrderSku($each, $skus_details->where('id', $each['sku_id'])->first());
            }
            elseif (array_key_exists('quantity', $each) && array_key_exists('price', $each)){
                $this->handleQuantityUpdateForEditedPrice($each);
            }
        }
    }

    private function getUpdatedProducts()
    {
        $current_products = collect($this->order->items()->get(['id', 'quantity', 'unit_price', 'sku_id'])->toArray());
        $request_products = collect(json_decode($this->skus, true));
        $updatedProducts = [];
        $current_products->each(function ($current_product) use ($request_products, &$updatedProducts) {
            $temp_product = [];
            if ($request_products->contains('id', $current_product['id'])) {
                $updating_product = $request_products->where('id', $current_product['id'])->first();

                if ($updating_product['quantity'] != $current_product['quantity']) {
                    $temp_product['id'] = $updating_product['id'];
                    $temp_product['quantity'] = $updating_product['quantity'];
                    $temp_product['quantity_changing_info'] = $this->getQuantityChangingDetails($current_product, $updating_product);
                }
                if (isset($updating_product['price']) && ($updating_product['price'] != $current_product['unit_price'])) {
                    $temp_product['price'] = $updating_product['price'];
                }
            }
            if ($temp_product) {
                $temp_product['id'] = $current_product['id'];
                $temp_product['sku_id'] = $current_product['sku_id'];
                $temp_product['previous_unit_price'] = $current_product['unit_price'];
                $updatedProducts [] = $temp_product;
            }
        });
        return $updatedProducts;
    }


    private function updateOrderSkuPriceOnly(array $order_sku)
    {
        $product = $this->order->orderSkus()->where('id', $order_sku['id'])->first();
        if ($product) {
            $product->unit_price = $order_sku['price'];
            return $product->save();
        }
        return false;
    }

    private function handleQuantityUpdateForOrderSku(array $product, array|null $sku_details)
    {
        $current_sku_price = $sku_details['sku_channel'][0]['price'];
        //handle when price same and quantity does not matter
        if($product['previous_unit_price'] == $current_sku_price) { //price is same so we are changing the quantity in order_skus
            $this->updateOrderSkuQuantityForSamePrice($product);
        }
        //handle when price changed and quantity increased
        elseif ($product['previous_unit_price'] != $current_sku_price && $product['quantity_changing_info']['type'] == self::QUANTITY_INCREASED) {
            $this->createOrderSkuForNewPriceQuantity($product);
        }
        //handle when price changed and quantity decreased
        elseif ($product['previous_unit_price'] == $current_sku_price && $product['quantity_changing_info']['type'] == self::QUANTITY_DECREASED) {
            $this->updateOrderSkuQuantityForSamePrice($product);
        }


    }

    private function getSkuDetails($sku_ids, $sales_channel_id)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&channel_id='.$sales_channel_id;
        $response = $this->client->get($url);
        return $response['skus'];
    }

    private function getUpdatedProductsSkuDetails(array $updated_products): Collection|bool
    {
        $updated_products_sku_ids =  $this->order->orderSkus()
            ->whereIn('id', array_column($updated_products, 'id'))
            ->where('sku_id', '<>', null )
            ->pluck('sku_id')
            ->toArray();
        if($updated_products_sku_ids){
            return collect($this->getSkuDetails($updated_products_sku_ids, $this->order->sales_channel_id));
        } else {
            return false;
        }

    }

    private function getQuantityChangingDetails($current_product, array $updating_product): array
    {
        $data = [];
        if ($updating_product['quantity'] > $current_product['quantity']) {
            $data['type'] = self::QUANTITY_INCREASED;
            $data['value'] = $updating_product['quantity'] - $current_product['quantity'];
        }
        else if ($current_product['quantity'] > $updating_product['quantity']) {
            $data['type'] = self::QUANTITY_DECREASED;
            $data['value'] = $current_product['quantity'] - $updating_product['quantity'];
        }
        return $data;
    }

    private function createOrderSkuForNewPriceQuantity($product)
    {
        $order_sku = $this->order->orderSkus()->where('id', $product['id'])->get(['name', 'sku_id as id', 'details', 'quantity'])->first();
        $order_sku->quantity = $product['quantity_changing_info']['value'];
        if(isset($product['price'])) {
            $order_sku->price = $product['price'];
        }
        $new_sku = json_decode($order_sku->toJson());
        /** @var Creator $creator */
        $creator = App::make(Creator::class);
        $creator->setOrder($this->order)->setSkus([$new_sku])->create();
    }

    private function updateOrderSkuQuantityForSamePrice(array $product)
    {
        $order_sku = $this->order->orderSkus()->where('id', $product['id'])->first();
        $order_sku->quantity = $product['quantity'];
        $order_sku->save();
    }

    private function handleNullOrderSkuItem(array $product)
    {
        //price not set or if set than if equal to previous price then update the quantity only
        if( !isset($product['price']) || (isset($product['price']) && $product['price'] == $product['previous_unit_price']) ){
            $this->updateOrderSkuQuantityForSamePrice($product);
        }
        //price set but no quantity change only update the price
        elseif (!isset($product['quantity']) && isset($product['price'])) {
            $this->updateOrderSkuPriceOnly($product);
        }
        //price and quantity both changed
        elseif (isset($product['price']) && isset($product['quantity']) ){
            //price changed and quantity increased then create new order sku
            if (($product['price'] != $product['previous_unit_price']) && ($product['quantity_changing_info']['type'] == self::QUANTITY_INCREASED)) {
                $this->createOrderSkuForNullSkuItem($product);
            }
            //price not same but quantity decreased then order sku update
            elseif (($product['price'] != $product['previous_unit_price']) && ($product['quantity_changing_info']['type'] == self::QUANTITY_DECREASED)){
                $this->updateOrderSkuPriceAndQuantity($product);
            }
        }
    }

    private function createOrderSkuForNullSkuItem(array $product)
    {
        $order_sku = $this->order->orderSkus()->where('id', $product['id'])->first();
        $order_sku->quantity = $product['quantity_changing_info']['value'];
        $order_sku->unit_price = $product['price'];
        $new_sku = $order_sku->toArray();
        $this->orderSkuRepository->create($new_sku);
    }

    private function updateOrderSkuPriceAndQuantity(array $product)
    {
        $order_sku = $this->order->orderSkus()->where('id', $product['id'])->first();
        $order_sku->quantity = $product['quantity_changing_info']['value'];
        $order_sku->unit_price = $product['price'];
        $order_sku->save();
    }

    private function handleQuantityUpdateForEditedPrice(array $product)
    {
        if($product['price'] != $product['previous_unit_price'] && $product['quantity_changing_info']['type'] == self::QUANTITY_INCREASED) {
            $this->createOrderSkuForNewPriceQuantity($product);
        }
    }


}
