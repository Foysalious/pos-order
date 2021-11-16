<?php namespace App\Services\Order\Objects;


class OrderPaymentLinkObject
{
    protected $paymentLinkDetails;
    protected ?int $id;
    protected ?string $status;
    protected ?string $link;
    protected ?float $amount;
    protected $created_at;

    /**
     * @param int|null $id
     * @return OrderPaymentLinkObject
     */
    public function setId(?int $id): OrderPaymentLinkObject
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string|null $status
     * @return OrderPaymentLinkObject
     */
    public function setStatus(?string $status): OrderPaymentLinkObject
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string|null $link
     * @return OrderPaymentLinkObject
     */
    public function setLink(?string $link): OrderPaymentLinkObject
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @param float|null $amount
     * @return OrderPaymentLinkObject
     */
    public function setAmount(?float $amount): OrderPaymentLinkObject
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return OrderPaymentLinkObject
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

}
