<?php namespace App\Services\Order\Refund;

use App\Exceptions\OrderException;
use App\Models\OrderSku;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Refund\Objects\AddRefundTracker;
use App\Services\Order\Refund\Objects\ProductChangeTracker;
use App\Services\OrderSku\BatchManipulator;
use App\Services\Product\StockManager;
use App\Traits\ResponseAPI;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateProductInOrder extends ProductOrder
{
    use ResponseAPI;
    private array $refunded_items_obj = [];
    private array $added_items_obj = [];
    private float $refunded_amount = 0;
    private array $stockUpdateData = [];

    /**
     * @throws OrderException
     */
    public function update() : array
    {
        $updated_products = $this->getUpdatedProducts();
        $skus_details = $this->getUpdatedProductsSkuDetails($updated_products);
        $this->checkStockAvailability($updated_products, $skus_details);
        foreach ($updated_products as $product) {
            /** @var $product ProductChangeTracker */
            if($product->getSkuId() == null && $product->isQuantityChanged())
                $this->handleNullSkuItemInOrder($product);
            elseif ($product->isQuantityChanged()) {
                $this->handleQuantityUpdateForOrderSku($product, $skus_details->where('id', $product->getSkuId())->first());
            }
        }
        $this->makeStockUpdateDataForProductsChanges($updated_products,$skus_details);
        $this->calculateRefundedAmountOfReturnedProducts();
        return [
            'refunded_amount' => $this->refunded_amount,
            'added_products' =>  $this->added_items_obj,
            'refunded_products' => $this->refunded_items_obj
        ];
    }

    private function getUpdatedProducts() : array
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
                ->setPreviousQuantity($current_product['quantity']);
            if ($request_products->contains('order_sku_id', $current_product['id'])) {
                $updating_product = $request_products->where('order_sku_id', $current_product['id'])->first();
                if ($updating_product['quantity'] != $current_product['quantity']) {
                    $updatedFlag = true;
                }
                if (isset($updating_product['price'])) {
                    $product_obj->setCurrentUnitPrice($updating_product['price']);
                    $updatedFlag = true;
                }
                if(!isset($updating_product['price']) && $updating_product['id'] == null) {
                    $product_obj->setCurrentUnitPrice($current_product['unit_price']);
                }
            }
            if ($updatedFlag) {
                $product_obj->setCurrentQuantity($updating_product['quantity']);
                $updatedProducts [] = $product_obj;
            }
        });
        return $updatedProducts;
    }

    /**
     * @throws OrderException
     */
    private function handleQuantityUpdateForOrderSku(ProductChangeTracker $product, array|null $sku_details)
    {
        $sku_channel = collect($sku_details['sku_channel'])->where('channel_id', $this->order->sales_channel_id)->first();
        if(!$product->isCurrentUnitPriceSet()) $product->setCurrentUnitPrice($sku_channel['price']);

        //handle when price same and quantity does not matter
        if ( !$product->isPriceChanged()) {
            $this->updateOrderSkuQuantityForSamePrice($product, $sku_details);
        }
        //handle when price changed and quantity increased
        elseif ( $product->isPriceChanged() && $product->isQuantityIncreased()) {
            $this->createOrderSkuForNewPriceQuantity($product);
        } else {
            throw new OrderException('Can not update item by price changing and quantity same or decreasing', 400);
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

    private function createOrderSkuForNewPriceQuantity(ProductChangeTracker $product)
    {
        $new_sku['id'] = $product->getSkuId();
        $new_sku['quantity'] = $product->getQuantityChangedValue();
        $new_sku['price'] = $product->getCurrentUnitPrice();
        $new_sku = (object) $new_sku;
        $new_order_sku = $this->orderSkuCreator->setIsPaymentMethodEmi($this->isPaymentMethodEmi)->setOrder($this->order)->setSkus([$new_sku])->create();
        $this->added_items_obj[] = $this->makeObject($product,$new_order_sku[0],$new_order_sku[0]);
    }

    private function updateOrderSkuQuantityForSamePrice(ProductChangeTracker $product, ?array $sku_details=null)
    {
        /** @var OrderSku $order_sku */
        $order_sku = $this->order->orderSkus()->where('id', $product->getOrderSkuId())->first();
        $order_sku->quantity = $product->getCurrentQuantity();
        $old_order_sku = clone $order_sku;
        if($sku_details){
            $order_sku->batch_detail = $this->calculateUpdatedBatchDetail($product,$sku_details['batches'],$order_sku);
        }
        $order_sku->save();
        if ($product->isQuantityDecreased()) {
            $this->refunded_items_obj [] = $this->makeObject($product, $old_order_sku, $order_sku);
        } else {
            $this->added_items_obj [] = $this->makeObject($product, $old_order_sku, $order_sku);
        }
    }

    /**
     * @throws OrderException
     */
    private function handleNullSkuItemInOrder(ProductChangeTracker $product)
    {
        //price not changed, only quantity changed
        if (!$product->isPriceChanged()) {
            $this->updateOrderSkuQuantityForSamePrice($product);
        }
        //price updated and quantity increased
        elseif ($product->isQuantityIncreased() && $product->isPriceChanged()) {
            $this->createOrderSkuForNullSkuItem($product);
        } else {
            throw new OrderException('Can not update quick sell item by price changing and quantity same or decreasing', 400);
        }
    }

    private function createOrderSkuForNullSkuItem(ProductChangeTracker $product)
    {
        /** @var OrderSku $order_sku */
        $order_sku = $this->order->orderSkus()->where('id', $product->getOrderSkuId())->first();
        $order_sku->quantity = $product->getQuantityChangedValue();
        $order_sku->unit_price = $product->getCurrentUnitPrice();
        $new_sku = $order_sku->toArray();
        $new_sku['batch_detail'] = json_encode(["id"=> null, "price" => $product->getCurrentUnitPrice(), "quantity" => $product->getQuantityChangedValue()]);
        $new_order_sku = $this->orderSkuRepository->create($new_sku);
        $this->added_items_obj [] = $this->makeObject($product, $order_sku, $new_order_sku);
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

    /**
     * @param array $updated_products
     * @param bool|Collection $skus_details
     */
    private function makeStockUpdateDataForProductsChanges(array $updated_products, bool|Collection $skus_details)
    {
        if (!$skus_details) return;
        foreach ($updated_products as $product) {
            /** @var $product ProductChangeTracker */
            if ($product->getSkuId() == null) continue;
            $product_detail = $skus_details->where('id', $product->getSkuId())->first();
            $this->stockManager->setOrder($this->order)->setSku($product_detail);
            if ($product->isQuantityIncreased()) {
                $this->stockUpdateData [] = [
                    'sku_detail' => $product_detail,
                    'quantity' => $product->getQuantityIncreasedValue(),
                    'operation' => StockManager::STOCK_DECREMENT,
                ];
            }
            if ($product->isQuantityDecreased()) {
                $this->stockUpdateData [] = [
                    'sku_detail' => $product_detail,
                    'quantity' => $product->getQuantityDecreasedValue(),
                    'operation' => StockManager::STOCK_INCREMENT,
                ];
            }
        }
    }

    private function calculateRefundedAmountOfReturnedProducts()
    {
        if(count($this->refunded_items_obj) == 0) return;
        $total_refund = 0;
        /** @var AddRefundTracker $item */
        foreach ($this->refunded_items_obj as $item) {
            if($item->isQuantityDecreased()) {
                $total_refund = $total_refund + ($item->getOldUnitPrice() * $item->getQuantityDecreasedValue());
            }
        }
        $this->refunded_amount = $total_refund;
    }

    private function calculateUpdatedBatchDetail(ProductChangeTracker $product, array $sku_batch, OrderSku $order_sku): bool|string
    {
        /** @var BatchManipulator $manipulator */
        $manipulator = App::make(BatchManipulator::class);
        return $manipulator->setBatchDetail($order_sku->batch_detail)
            ->setUpdatedQuantity($product->getCurrentQuantity())->setPreviousQuantity($product->getPreviousQuantity())->setSkuBatch($sku_batch)->updateBatchDetail()->getUpdatedBatchDetail();
    }

    /**
     * @param ProductChangeTracker $product
     * @param OrderSku $old_order_sku
     * @param OrderSku $new_order_sku
     * @return AddRefundTracker
     */
    private function makeObject(ProductChangeTracker $product, OrderSku $old_order_sku, OrderSku $new_order_sku)
    {
        /** @var AddRefundTracker $obj */
        $obj = App::make(AddRefundTracker::class);
        return $obj->setSkuId($product->getSkuId())
            ->setCurrentQuantity($product->getCurrentQuantity())
            ->setPreviousQuantity($product->getPreviousQuantity())
            ->setOldUnitPrice($product->getOldUnitPrice())
            ->setCurrentUnitPrice($product->getCurrentUnitPrice())
            ->setOrderSkuId($new_order_sku->id)
            ->setOldBatchDetail($old_order_sku->batch_detail)
            ->setUpdatedBatchDetail($new_order_sku->batch_detail);

    }

    /**
     * @return array
     */
    public function getStockUpdateData(): array
    {
        return $this->stockUpdateData;
    }


}
