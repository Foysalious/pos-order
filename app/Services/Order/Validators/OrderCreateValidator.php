<?php namespace App\Services\Order\Validators;

class OrderCreateValidator extends Validator
{
    public function hasError()
    {
        if ($this->isOutOfStock()) return ['code' => 421, 'msg' => 'Product out of stock.'];
    }

    private function isOutOfStock()
    {
        return false;
    }

    public function setProducts(array $products)
    {
        $this->products = $products;
        return $this;
    }

}
