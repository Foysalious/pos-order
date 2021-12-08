<?php namespace App\Services\Order\Refund;

use App\Models\OrderSku;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Refund\Objects\AddRefundTracker;
use App\Services\Order\Refund\Objects\ProductChangeTracker;
use App\Services\OrderSku\BatchManipulator;
use App\Services\Product\StockManager;
use App\Traits\ModificationFields;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateProductInOrder extends ProductOrder
{
    use ModificationFields;
    private array $refunded_items_obj = [];
    private array $added_items_obj = [];
    private float $refunded_amount = 0;
    private array $stockUpdateData = [];
    private array $productChangeTrackerList = [];


    /**
     * @return array
     */
    public function getStockUpdateData(): array
    {
        return $this->stockUpdateData;
    }

    /**
     * @param array $productChangeTrackerList
     * @return UpdateProductInOrder
     */
    public function setProductChangeTrackerList(array $productChangeTrackerList)
    {
        $this->productChangeTrackerList = $productChangeTrackerList;
        return $this;
    }

    /**
     * @return array
     */
    public function update() : array
    {
        $skus_details = $this->getUpdatedProductsSkuDetails();
        $this->checkStockAvailability($skus_details);
        foreach ($this->productChangeTrackerList as $product) {
            /** @var $product ProductChangeTracker */
            if($product->isVatPercentageChanged()) {
                $this->updateVatPercentage($product);
            }
            if($product->isPriceChanged() && !$product->isQuantityChanged()){
                $this->updatePrice($product);
            }
            if($product->isDiscountChanged()){
                $this->updateDiscount($product);
            }
            if($product->isQuantityChanged()) {
                $this->handleQuantityUpdateForOrderSku($product, $skus_details->where('id', $product->getSkuId())->first());
            }
        }
        $this->makeStockUpdateDataForProductsChanges($skus_details);
        $this->calculateRefundedAmountOfReturnedProducts();
        return [
            'refunded_amount' => $this->refunded_amount,
            'added_products' =>  $this->added_items_obj,
            'refunded_products' => $this->refunded_items_obj
        ];
    }

    /**
     * @param ProductChangeTracker $product
     * @param array|null $sku_detail
     */
    private function handleQuantityUpdateForOrderSku(ProductChangeTracker $product, array|null $sku_detail)
    {
        if (!$product->isPriceChanged()) {
            $this->updateOrderSkuQuantityForSamePrice($product, $sku_detail);
        }
    }

    private function getUpdatedProductsSkuDetails(): Collection
    {
        $sku_ids = collect();
        /** @var ProductChangeTracker $each */
        foreach ($this->productChangeTrackerList as $each) {
            $sku_id = $each->getSkuId();
            if ($sku_id) $sku_ids->add($sku_id);
        }
        if($sku_ids->count() > 0){
            return collect($this->getSkuDetails($sku_ids->toArray(), $this->order->sales_channel_id));
        } else {
            return collect();
        }
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

    private function checkStockAvailability(Collection $skus_details)
    {
        if ($skus_details->isEmpty() || $this->order->sales_channel_id == SalesChannelIds::POS) return; // null sku_id products have no $sku_details OR  POS is not required to check stock

        foreach ($this->productChangeTrackerList as $product) {
            /** @var $product ProductChangeTracker */
            if ($product->getSkuId() == null) continue;
            $product_detail = $skus_details->where('id', $product->getSkuId())->first();
            if ($product->isQuantityIncreased()) {
                if ($product->getQuantityIncreasedValue() > $product_detail['stock']) throw new NotFoundHttpException("Product #" . $product->getSkuId() . " Not Enough Stock");
            }
        }
    }

    /**
     * @param Collection $skus_details
     */
    private function makeStockUpdateDataForProductsChanges(Collection $skus_details)
    {
        if ($skus_details->isEmpty()) return;
        foreach ($this->productChangeTrackerList as $product) {
            /** @var $product ProductChangeTracker */
            if ($product->getSkuId() == null) continue;
            $product_detail = $skus_details->where('id', $product->getSkuId())->first();
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

    private function updateVatPercentage(ProductChangeTracker $product)
    {
        $order_sku = $this->order->orderSkus->where('id', $product->getOrderSkuId())->first();
        $order_sku->vat_percentage = $product->getCurrentVatPercentage();
        $order_sku->update($this->modificationFields(false,true));
    }

    private function updatePrice(ProductChangeTracker $product)
    {
        $order_sku = $this->order->orderSkus->where('id', $product->getOrderSkuId())->first();
        $order_sku->unit_price = $product->getCurrentUnitPrice();
        $order_sku->update($this->modificationFields(false,true));
    }

    private function updateDiscount(ProductChangeTracker $product)
    {
        $order_sku_discount = $this->order->orderSkus->where('id',$product->getOrderSkuId())->first()->discount;
        $discount_detail = $product->getCurrentDiscountDetails();
        $original_amount = $discount_detail['discount'];
        $is_percentage = $discount_detail['is_percentage'];
        $amount = $this->calculateTotalDiscount($discount_detail,$product);
        if($order_sku_discount){
            $order_sku_discount->original_amount = $original_amount;
            $order_sku_discount->is_percentage = $is_percentage;
            $order_sku_discount->amount = $amount;
            $order_sku_discount->update($this->modificationFields(false,true));
        } else {
            if($discount_detail['discount'] > 0) {
                $this->discountRepository->create($this->withCreateModificationField([
                    'order_id' => $this->order->id,
                    'type' => DiscountTypes::SKU,
                    'amount' => $amount,
                    'original_amount' => $original_amount,
                    'is_percentage' => $is_percentage,
                    'type_id' => $product->getOrderSkuId(),
                ]));
            }
        }
    }

    private function calculateTotalDiscount(array $discount_detail, ProductChangeTracker $productChangeTracker) : float
    {
        $price = $productChangeTracker->getCurrentUnitPrice();
        $quantity = $productChangeTracker->getCurrentQuantity();
        if($discount_detail['is_percentage']) {
            return ($price*$quantity*$discount_detail['discount']) / 100;
        } else {
            return $quantity*$discount_detail['discount'];
        }
    }

}
