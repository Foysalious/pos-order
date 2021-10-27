<?php namespace App\Services\Order;

use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Order\Refund\Objects\ProductChangeTracker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class OrderComparator
{
    private bool $productAddedFlag = FALSE;
    private bool $productDeletedFlag = FALSE;
    private bool $productUpdatedFlag = FALSE;

    private array $addedProducts = [];
    private array $deletedProducts = [];
    private array $productChangeTrackerList = [];

    public Order $order;
    public $skus;

    /**
     * @param Order $order
     * @return OrderComparator
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param $skus
     * @return OrderComparator
     */
    public function setOrderNewSkus($skus)
    {
        $this->skus = $skus;
        return $this;
    }

    public function compare()
    {
        $this->checkForProductAdditionAndDeletion();
        $this->checkUpdatesInProduct();
        return $this;
    }

    private function checkForProductAdditionAndDeletion()
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = collect(json_decode($this->skus, true))->pluck('order_sku_id');

        if ($current_products->diff($request_products)->isEmpty() && $request_products->diff($current_products)->isEmpty()) {
            $this->productAddedFlag = FALSE;
            $this->productDeletedFlag = FALSE;
        } else {
            $this->lookForAddedProducts($current_products,$request_products);
            $this->lookForDeletedProducts($current_products,$request_products);
        }
    }

    /**
     * @param Collection $current_products
     * @param Collection $request_products
     */
    private function lookForAddedProducts($current_products, $request_products)
    {
        if($request_products->diff($current_products)->isNotEmpty()) {
            $this->productAddedFlag = TRUE;
            $this->addedProducts = $request_products->diff($current_products)->toArray();
        }
    }

    /**
     * @param Collection $current_products
     * @param Collection $request_products
     */
    private function lookForDeletedProducts($current_products, $request_products)
    {
        if ($current_products->diff($request_products)->isNotEmpty()) {
            $this->productDeletedFlag = TRUE;
            $this->deletedProducts = $current_products->diff($request_products)->toArray();
        }
    }

    private function checkUpdatesInProduct() {
        $current_products = $this->order->orderSkus;
        $request_products = collect(json_decode($this->skus, true));
        $current_products->each(function ($current_product) use ($request_products){
            if ($request_products->contains('order_sku_id',$current_product->id)) {
                $updating_product = $request_products->where('order_sku_id', $current_product->id)->first();
                /** @var ProductChangeTracker $product_obj */
                $product_obj = App::make(ProductChangeTracker::class);
                $product_obj->setOrderSkuId($current_product->id)
                    ->setSkuId($current_product->sku_id)
                    ->setOldUnitPrice($current_product->unit_price)
                    ->setPreviousQuantity($current_product->quantity)
                    ->setPreviuosVatPercentage($current_product->vat_percentage)
                    ->setPreviousDiscountDetails($current_product->discount)
                    ->setCurrentUnitPrice($updating_product['price'])
                    ->setCurrentQuantity($updating_product['quantity'])
                    ->setCurrentVatPercentage($updating_product['vat_percentage'])
                    ->setCurrentDiscountDetails(['discount' => $updating_product['discount'], 'is_percentage' => $updating_product['is_discount_percentage']]);

                $this->validateUpdate($product_obj);

                if ($product_obj->isQuantityChanged() || $product_obj->isPriceChanged() || $product_obj->isVatPercentageChanged() || $product_obj->isDiscountChanged()) {
                    $this->productUpdatedFlag = True;
                    $this->productChangeTrackerList [] = $product_obj;
                }
            }
        });
    }

    /**
     * @return bool
     */
    public function isProductAdded(): bool
    {
        return $this->productAddedFlag;
    }

    /**
     * @return bool
     */
    public function isProductDeleted(): bool
    {
        return $this->productDeletedFlag;
    }

    /**
     * @return bool
     */
    public function isProductUpdated(): bool
    {
        return $this->productUpdatedFlag;
    }

    /**
     * @return array
     */
    public function getAddedProducts(): array
    {
        return array_values($this->addedProducts);
    }

    /**
     * @return array
     */
    public function getDeletedProducts(): array
    {
        return array_values($this->deletedProducts);
    }

    /**
     * @return array
     */
    public function getProductChangeTrackerList(): array
    {
        return $this->productChangeTrackerList;
    }

    /**
     * @throws OrderException
     */
    private function validateUpdate(ProductChangeTracker $product_obj)
    {
        if ($product_obj->isQuantityDecreased() && ($product_obj->isPriceChanged() || $product_obj->isVatPercentageChanged() || $product_obj->isDiscountChanged())) {
            throw new OrderException('updating discount/vat-percentage/price is not allowed while quantity decreasing', 400);
        }
    }

}


