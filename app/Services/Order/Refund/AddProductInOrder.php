<?php namespace App\Services\Order\Refund;

use App\Exceptions\OrderException;
use App\Traits\ModificationFields;
use Illuminate\Validation\ValidationException;

class AddProductInOrder extends ProductOrder
{
    use ModificationFields;
    private array $added_products = [];
    private array $stockUpdateData = [];

    /**
     * @throws OrderException
     * @throws ValidationException
     */
    public function update(): array
    {
        $all_items = $this->getAddedItems();
        foreach ($all_items as $each_item) {
            if(!is_null($each_item->id)) unset($each_item->price);
        }
        // false for no check in backend for updating emi order by POS
        $this->isPaymentMethodEmi = false;
        $new_order_skus = $this->orderSkuCreator->setOrder($this->order)->setIsPaymentMethodEmi($this->isPaymentMethodEmi)
            ->setSkus($all_items)->create();
        $this->stockUpdateData = $this->orderSkuCreator->getStockDecreasingData();
        $this->addOrderSkusInReturnData($new_order_skus);

        return $this->added_products;
    }

    private function getAddedItems(): array
    {
        return $this->skus->whereNull('order_sku_id')->toArray();
    }

    private function addOrderSkusInReturnData(array $new_order_skus)
    {
        foreach ($new_order_skus as $each) {
            $this->added_products [] = $each;
        }
    }

    /**
     * @return array
     */
    public function getStockUpdateData(): array
    {
        return $this->stockUpdateData;
    }

}
