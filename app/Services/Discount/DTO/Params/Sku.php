<?php namespace App\Services\Discount\DTO\Params;


class Sku extends SetParams
{
    private $discount;
    private $amount;
    private $orderSkuId;
    private $originalAmount;
    private $isPercentage;


    /**
     * @param $discount
     * @return $this
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setOriginalAmount($originalAmount)
    {
        $this->originalAmount = $originalAmount;
        return $this;
    }

    /**
     * @param $is_percentage
     * @return $this
     */
    public function setIsPercentage($is_percentage)
    {
        $this->isPercentage = $is_percentage;
        return $this;
    }

    /**
     * @param $orderSkuId
     * @return $this
     */
    public function setOrderSkuId($orderSkuId)
    {
        $this->orderSkuId = $orderSkuId;
        return $this;
    }

    public function getData()
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'original_amount' => $this->originalAmount,
            'is_percentage' => $this->isPercentage,
            'type_id' => $this->orderSkuId,
        ];
    }
}
