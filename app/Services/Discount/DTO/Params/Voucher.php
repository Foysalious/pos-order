<?php namespace App\Services\Discount\DTO\Params;


use App\Services\Order\PriceCalculation;

class Voucher extends SetParams
{
    private float $totalAmount;
    private int $isPercentage;
    private array $discountDetails;

    /**
     * @param mixed $totalAmount
     * @return Voucher
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    /**
     * @param mixed $isPercentage
     * @return Voucher
     */
    public function setIsPercentage($isPercentage)
    {
        $this->isPercentage = $isPercentage;
        return $this;
    }

    /**
     * @param array $discountDetails
     * @return Voucher
     */
    public function setDiscountDetails(array $discountDetails)
    {
        $this->discountDetails = $discountDetails;
        return $this;
    }

    public function getData() : array
    {
        return [
            'type' => $this->type,
            'amount' => $this->getApplicableAmount(),
            'original_amount' => $this->totalAmount,
            'is_percentage' => $this->isPercentage,
            'discount_details' => json_encode($this->discountDetails)
        ];
    }

    private function getApplicableAmount() : float
    {
        /** @var $priceCalculation PriceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        return $this->isPercentage ? (($this->totalAmount / 100) * $priceCalculation->setOrder($this->order)->getProductDiscountedPrice()) : $this->totalAmount;
    }
}
