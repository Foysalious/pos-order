<?php namespace App\Services\Order\Objects;


class OrderPaymentObject
{
    protected $paymentDetails;
    protected ?float $amount;
    protected ?string $method;
    protected $created_at;

    public function setPaymentDetails($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;
        return $this;
    }

    public function build()
    {
        $this->amount = $this->paymentDetails->amount;
        $this->method = $this->paymentDetails->method;
        $this->created_at = $this->paymentDetails->created_at;
        return $this;
    }
}
