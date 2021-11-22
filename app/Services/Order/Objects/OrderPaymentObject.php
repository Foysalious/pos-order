<?php namespace App\Services\Order\Objects;


class OrderPaymentObject
{
    protected ?float $amount;
    protected ?string $method;
    protected $created_at;

    /**
     * @param float|null $amount
     * @return OrderPaymentObject
     */
    public function setAmount(?float $amount): OrderPaymentObject
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string|null $method
     * @return OrderPaymentObject
     */
    public function setMethod(?string $method): OrderPaymentObject
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return OrderPaymentObject
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }


}
