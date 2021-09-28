<?php namespace App\Services\Order\Refund;

use App\Exceptions\OrderException;
use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Traits\ModificationFields;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AddProductInOrder extends ProductOrder
{
    use ModificationFields;
    private array $added_products = [];

    /**
     * @throws BaseClientServerError
     * @throws OrderException
     * @throws ValidationException
     */
    public function update(): array
    {
        $all_items = $this->getAddedItems();
        $new_order_skus = $this->orderSkuCreator->setOrder($this->order)->setIsPaymentMethodEmi($this->isPaymentMethodEmi)
            ->setSkus($all_items)->create();
        $this->addOrderSkusInReturnData($new_order_skus);
        return $this->added_products;
    }

    private function getAddedItems(): array
    {
        $current_products = $this->order->items()->pluck('id');
        $request_products = $this->skus->pluck('id');
        $items = $request_products->diff($current_products);
        return $this->skus->whereIn('id',$items)->toArray();
    }

    private function addOrderSkusInReturnData(array $new_order_skus)
    {
        foreach ($new_order_skus as $each) {
            $this->added_products [] = $each;
        }
    }

}
