<?php namespace App\Services\Order\Refund;

use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Refund\Objects\AddRefundTracker;
use App\Services\Order\Refund\Objects\ProductChangeTracker;
use App\Services\OrderSku\BatchManipulator;
use App\Services\OrderSku\Creator;
use App\Services\OrderSku\OrderSkuDetailCreator;
use App\Services\Transaction\Constants\TransactionTypes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateProductInOrder extends ProductOrder
{
    const QUANTITY_INCREASED = 'increment';
    const QUANTITY_DECREASED = 'decrement';

    private array $refunded_items_obj = [];
    private array $added_items_obj = [];
    private float $refunded_amount = 0;

    public function update()
    {
        $updated_products = $this->getUpdatedProducts();
        $skus_details = $this->getUpdatedProductsSkuDetails($updated_products);
        $this->checkStockAvailability($updated_products, $skus_details);

        foreach ($updated_products as $product) {
            /** @var $product ProductChangeTracker */
            if($product->getSkuId() == null)
                $this->handleNullSkuItemInOrder($product);
            elseif ($product->isQuantityChanged() && $product->getSkuId() != null ) {
                $this->handleQuantityUpdateForOrderSku($product, $skus_details->where('id', $product->getSkuId())->first());
            }
        }
        $this->updateStockForProductsChanges($updated_products,$skus_details);
        $this->calculateAndRefundForUpdatedOrder();
        return [
            'refunded_amount' => $this->refunded_amount,
            'added_products' =>  $this->added_items_obj,
            'refunded_products' => $this->refunded_items_obj
        ];
    }

    private function getUpdatedProducts()
    {
        $current_products = collect($this->order->items()->get(['id', 'quantity', 'unit_price', 'sku_id'])->toArray());
        $request_products = collect(json_decode($this->skus, true));
        $updatedProducts = [];
        $current_products->each(function ($current_product) use ($request_products, &$updatedProducts) {
            $updatedFlag = false;
            /** @var ProductChangeTracker $product_obj */
            $product_obj = App::make(ProductChangeTracker::class);
            $product_obj->setOrderSkuId($current_product['id'])
                ->setSkuId($current_product['sku_id'])
                ->setOldUnitPrice($current_product['unit_price'])
                ->setCurrentUnitPrice($current_product['unit_price']);

            if ($request_products->contains('id', $current_product['id'])) {
                $updating_product = $request_products->where('id', $current_product['id'])->first();

                if ($updating_product['quantity'] != $current_product['quantity']) {
                    $updatedFlag = true;
                    $product_obj->setQuantity($updating_product['quantity']);
                    $product_obj->setCurrentQuantity($updating_product['quantity']);
                    $product_obj->setPreviousQuantity($current_product['quantity']);
                    $temp['quantity_changing_info'] = $this->getQuantityChangingDetails($current_product, $updating_product);
                    $product_obj->setQuantityIncreased($temp['quantity_changing_info']['type'] == self::QUANTITY_INCREASED);
                    $product_obj->setQuantityChangedValue($temp['quantity_changing_info']['value']);
                }
                if (isset($updating_product['price']) && ($updating_product['price'] != $current_product['unit_price'])) {
                    $updatedFlag = true;
                    $product_obj->setCurrentUnitPrice($updating_product['price']);
                }
            }
            if ($updatedFlag) {
                $updatedProducts [] = $product_obj;
            }
        });
        return $updatedProducts;
    }


    private function updateOrderSkuPriceOnly(ProductChangeTracker $product)
    {
        $order_sku = $this->order->orderSkus()->where('id', $product->getOrderSkuId())->first();
        if ($order_sku) {
            $order_sku->unit_price = $product->getCurrentUnitPrice();
            $order_sku->save();
        }
    }

    private function handleQuantityUpdateForOrderSku(ProductChangeTracker $product, array|null $sku_details)
    {
        $product->setCurrentUnitPrice($sku_details['sku_channel'][0]['price']);

        //handle when price same and quantity does not matter
        if ($product->isPriceChanged() == false) { //price is same so we are changing the quantity in order_skus
            $this->updateOrderSkuQuantityForSamePrice($product, $sku_details);
        }
        //handle when price changed and quantity increased
        elseif ( $product->isPriceChanged() && $product->isQuantityIncreased()) {
            $this->createOrderSkuForNewPriceQuantity($product);
        }
    }

    private function getUpdatedProductsSkuDetails(array $updated_products): Collection|bool
    {
        $orderSkuIds = [];
        array_walk($updated_products, function ($items) use (&$orderSkuIds){
            $orderSkuIds [] =  $items->getOrderSkuId();
        });
        $updated_products_sku_ids =  $this->order->orderSkus()
            ->whereIn('id', $orderSkuIds)
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

    private function createOrderSkuForNewPriceQuantity(ProductChangeTracker $product)
    {
        $new_sku['id'] = $product->getSkuId();
        $new_sku['quantity'] = $product->getQuantityChangedValue();
        $new_sku['price'] = $product->getCurrentUnitPrice();
        $new_sku = (object) $new_sku;
        /** @var Creator $creator */
        $creator = App::make(Creator::class);
        $new_order_sku = $creator->setOrder($this->order)->setSkus([$new_sku])->create();
        $this->added_items_obj[] = $this->makeObject($product,$new_order_sku[0],$new_order_sku[0]);
    }

    private function updateOrderSkuQuantityForSamePrice(ProductChangeTracker $product, $sku_details=null)
    {
        $order_sku = $this->order->orderSkus()->where('id', $product->getOrderSkuId())->first();
        $order_sku->quantity = $product->getQuantity();
        $old_order_sku = clone $order_sku;
        if($sku_details){
            $order_sku->details = $this->calculateUpdatedOrderSkuDetails($product,$sku_details['batches'],$order_sku);
        }
        $order_sku->save();
        if ($product->isQuantityDecreased()) {
            $this->refunded_items_obj [] = $this->makeObject($product, $old_order_sku, $order_sku);
        } else {
            $this->added_items_obj [] = $this->makeObject($product, $old_order_sku, $order_sku);
        }
    }

    private function handleNullSkuItemInOrder(ProductChangeTracker $product)
    {
        //price not set or if set than if equal to previous price then update the quantity only
        if ( $product->isPriceChanged() == false ) {
            $this->updateOrderSkuQuantityForSamePrice($product);
        }
        //price set but no quantity change only update the price
        elseif ( !$product->isQuantityChanged() && $product->isPriceChanged() ) {
            $this->updateOrderSkuPriceOnly($product);
        }
        //price and quantity both changed
        elseif ($product->isPriceChanged() && $product->isQuantityChanged()) {
            //price changed and quantity increased then create new order sku
            if ($product->isQuantityIncreased()) {
                $this->createOrderSkuForNullSkuItem($product);
            }
            //price not same but quantity decreased then order sku update
            elseif ($product->isQuantityDecreased()){
                $this->updateOrderSkuPriceAndQuantity($product);
            }
        }
    }

    private function createOrderSkuForNullSkuItem(ProductChangeTracker $product)
    {
        $order_sku = $this->order->orderSkus()->where('id', $product->getOrderSkuId())->first();
        $order_sku->quantity = $product->getQuantityChangedValue();
        $order_sku->unit_price = $product->getCurrentUnitPrice();
        $new_sku = $order_sku->toArray();
        $new_sku['details'] = json_encode(["id"=> null, "price" => $product->getCurrentUnitPrice(), "quantity" => $product->getQuantityChangedValue()]);
        $new_order_sku = $this->orderSkuRepository->create($new_sku);
        $this->added_items_obj [] = $this->makeObject($product, $order_sku, $new_order_sku);
    }

    private function updateOrderSkuPriceAndQuantity(ProductChangeTracker $product)
    {
        $order_sku = $this->order->orderSkus()->where('id', $product->getOrderSkuId())->first();
        $old_order_sku = clone $order_sku;
        $order_sku->quantity = $product->getQuantity();
        $order_sku->unit_price = $product->getCurrentUnitPrice();
        $order_sku->details = json_encode(["id"=> null, "price" => $product->getCurrentUnitPrice(), "quantity" => $product->getQuantity() ]);
        $updated = $order_sku->save();
        if ($updated && $product->isQuantityDecreased()) {
            $this->refunded_items_obj [] = $this->makeObject($product, $old_order_sku, $order_sku);
        } else {
            $this->added_items_obj [] = $this->makeObject($product, $old_order_sku, $order_sku);
        }
    }

    private function checkStockAvailability(array $updated_products, bool|Collection $skus_details)
    {
        if (!$skus_details || $this->order->sales_channel_id == SalesChannelIds::POS) return; // null sku_id products have no $sku_details OR  POS is not required to check stock

        foreach ($updated_products as $product) {
            /** @var $product ProductChangeTracker */
            if ($product->getSkuId() == null) continue;
            $product_detail = $skus_details->where('id', $product->getSkuId())->first();
            if ($product->isQuantityIncreased()) {
                if ($product->getQuantityIncreasedValue() > $product_detail['stock']) throw new NotFoundHttpException("Product #" . $product->getSkuId() . " Not Enough Stock");
            }
        }
    }

    private function updateStockForProductsChanges(array $updated_products, bool|Collection $skus_details)
    {
        if (!$skus_details) return;
        foreach ($updated_products as $product) {
            /** @var $product ProductChangeTracker */
            if ($product->getSkuId() == null) continue;
            $product_detail = $skus_details->where('id', $product->getSkuId())->first();
            $this->stockManager->setOrder($this->order)->setSku($product_detail);
            if ($product->isQuantityIncreased()) {
                if ($this->stockManager->isStockMaintainable()) $this->stockManager->decrease($product->getQuantityChangedValue());
            }
            if ($product->isQuantityDecreased()) {
                $this->stockManager->increase($product->getQuantityChangedValue());
            }
        }
    }

    private function calculateAndRefundForUpdatedOrder()
    {
        if(count($this->refunded_items_obj) == 0) return;
        $total_refund = 0;
        /** @var AddRefundTracker $item */
        foreach ($this->refunded_items_obj as $item) {
            if($item->isQuantityDecreased()) {
                $total_refund = $total_refund + ($item->getOldUnitPrice() * $item->getQuantityChangedValue());
            }
        }
        $payment_data['order_id'] = $this->order->id;
        $payment_data['amount'] = $total_refund;
        $payment_data['method'] = PaymentMethods::CASH_ON_DELIVERY;
        $this->orderPaymentCreator->debit($payment_data);
        $this->refunded_amount = $total_refund;
    }

    private function calculateUpdatedOrderSkuDetails(ProductChangeTracker $product,$sku_batch,$order_sku)
    {
        /** @var BatchManipulator $manipulator */
        $manipulator = App::make(BatchManipulator::class);
        return $manipulator->setOrderSkuDetails($order_sku->details)
            ->setQuantity($product->getQuantity())->setSkuBatch($sku_batch)->updateBatchDetail()->getUpdatedSkuDetails();
    }

    private function makeObject(ProductChangeTracker $product, mixed $old_order_sku, $new_order_sku)
    {
        /** @var AddRefundTracker $obj */
        $obj = App::make(AddRefundTracker::class);
        return $obj->setSkuId($product->getSkuId())
            ->setQuantity($product->getQuantity())
            ->setOldUnitPrice($product->getOldUnitPrice())
            ->setCurrentUnitPrice($new_order_sku->unit_price)
            ->setOrderSkuId($product->getOrderSkuId())
            ->setQuantityDecreased($product->isQuantityDecreased())
            ->setQuantityChangedValue($product->getQuantityChangedValue())
            ->setOldBatchDetail($old_order_sku->details)
            ->setUpdatedBatchDetail($new_order_sku->details);

    }


}
