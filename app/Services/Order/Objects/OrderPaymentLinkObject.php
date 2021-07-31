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
     * @param mixed $paymentLinkDetails
     * @return OrderPaymentLinkObject
     */
    public function setPaymentLinkDetails($paymentLinkDetails)
    {
        $this->paymentLinkDetails = $paymentLinkDetails;
        return $this;
    }

    public function build()
    {
        $this->id = $this->paymentLinkDetails->id;
        $this->status = $this->paymentLinkDetails->status;
        $this->link = $this->paymentLinkDetails->link;
        $this->amount = $this->paymentLinkDetails->amount;
        $this->created_at = $this->paymentLinkDetails->created_at;
        return $this;
    }

}
