<?php namespace App\Services\Discount\DTO\Params;


use App\Services\Order\PriceCalculation;

class Voucher extends SetParams
{
    private $totalAmount;
    private $isPercentage;
    protected $type;
    protected $order;

    /**
     * @param mixed $order
     * @return Voucher
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

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
     * @param mixed $type
     * @return Voucher
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getData()
    {
        return [
            'type' => $this->type,
            'amount' => $this->getApplicableAmount(),
            'original_amount' => $this->totalAmount,
            'is_percentage' => $this->isPercentage,
        ];
    }

    private function getApplicableAmount()
    {
        /** @var $priceCalculation PriceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        return $this->isPercentage ? (($this->totalAmount / 100) * $priceCalculation->setOrder($this->order)->getTotalBill()) : $this->totalAmount;
    }
}
