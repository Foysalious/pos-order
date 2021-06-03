<?php


namespace App\Services\Order;


use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrderComparator
{
    private bool $productAddedFlag = FALSE;
    private bool $productDeletedFlag = FALSE;
    private bool $productUpdatedFlag = FALSE;

    private array $addedProducts = [];
    private array $deletedProducts = [];
    private array $updatedProducts = [];

    public Order $order;
    public Request $newOrder;

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
     * @param Request $newOrder
     * @return OrderComparator
     */
    public function setNewOrder(Request $newOrder)
    {
        $this->newOrder = $newOrder;
        return $this;
    }

    public function compare()
    {
        $this->checkForProductAdditionAndDeletion();
        $this->checkForProductUpdate();
        return $this;
//        dump('added',$this->productAddedFlag, $this->addedProducts);
//        dump('deleted',$this->productDeletedFlag, $this->deletedProducts);
//        dump('updated',$this->productUpdatedFlag, $this->updatedProducts);
//        die;
    }

    private function checkForProductAdditionAndDeletion()
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = collect(json_decode($this->newOrder->skus, true))->pluck('id');

        if ($current_products->diff($request_products)->isEmpty() && $request_products->diff($current_products)->isEmpty()) {
            $this->productAddedFlag = FALSE;
            $this->productDeletedFlag = FALSE;
        } else {
            $this->findAddedProducts($current_products,$request_products);
            $this->findDeletedProducts($current_products,$request_products);
        }
    }

    /**
     * @param Collection $current_products
     * @param Collection $request_products
     */
    private function findAddedProducts($current_products, $request_products)
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
    private function findDeletedProducts($current_products, $request_products)
    {
        if ($current_products->diff($request_products)->isNotEmpty()) {
            $this->productDeletedFlag = TRUE;
            $this->deletedProducts = $current_products->diff($request_products)->toArray();
        }
    }

    private function checkForProductUpdate() {
        $current_products = collect($this->order->items()->get(['id','quantity','unit_price'])->toArray());
        $request_products = collect(json_decode($this->newOrder->skus, true));
        $current_products->each(function ($current_product) use ($request_products){
            if ($request_products->contains('id',$current_product['id'])) {
                $updating_product = $request_products->where('id', $current_product['id'])->first();

                if ($updating_product['quantity'] != $current_product['quantity']){
                    $this->productUpdatedFlag = true;
                    $this->updatedProducts [] = $updating_product['id'];
                }
                if (isset($updating_product['price']) && ($updating_product['price'] != $current_product['unit_price'])){
                    $this->productUpdatedFlag = true;
                    if (array_search($updating_product['id'],$this->updatedProducts) === FALSE ) {
                        $this->updatedProducts [] = $updating_product['id'];
                    }
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
    public function getUpdatedProducts(): array
    {
        return array_values($this->updatedProducts);
    }

}


