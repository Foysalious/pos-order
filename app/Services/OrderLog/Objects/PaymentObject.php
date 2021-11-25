<?php namespace App\Services\OrderLog\Objects;


use App\Models\OrderPayment;
use JsonSerializable;

class PaymentObject implements JsonSerializable
{
    private ?int $id;
    private ?int $order_id;
    private ?float $amount;
    private ?string $transaction_type;
    private ?string $method;
    private ?string $method_details;
    private ?int $emi_month;
    private ?float $interest;
    private ?string $created_by_name;
    private ?string $updated_by_name;
    private ?string $created_at;
    private ?string $updated_at;
    private ?string $deleted_at;

    private OrderPayment $payment;

    /**
     * @param OrderPayment $payment
     * @return $this
     */
    public function setPayment(OrderPayment $payment): PaymentObject
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @param mixed $id
     * @return PaymentObject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $order_id
     * @return PaymentObject
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return PaymentObject
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $transaction_type
     * @return PaymentObject
     */
    public function setTransactionType($transaction_type)
    {
        $this->transaction_type = $transaction_type;
        return $this;
    }

    /**
     * @param mixed $method
     * @return PaymentObject
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param mixed $method_details
     * @return PaymentObject
     */
    public function setMethodDetails($method_details)
    {
        $this->method_details = $method_details;
        return $this;
    }

    /**
     * @param mixed $emi_month
     * @return PaymentObject
     */
    public function setEmiMonth($emi_month)
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return PaymentObject
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $created_by_name
     * @return PaymentObject
     */
    public function setCreatedByName($created_by_name)
    {
        $this->created_by_name = $created_by_name;
        return $this;
    }

    /**
     * @param mixed $updated_by_name
     * @return PaymentObject
     */
    public function setUpdatedByName($updated_by_name)
    {
        $this->updated_by_name = $updated_by_name;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return PaymentObject
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @param mixed $updated_at
     * @return PaymentObject
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @param mixed $deleted_at
     * @return PaymentObject
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->payment->id,
            'order_id' => $this->payment->order_id,
            'amount' => $this->payment->amount,
            'transaction_type' => $this->payment->transaction_type,
            'method' => $this->payment->method,
            'method_details' => $this->payment->method_details,
            'emi_month' => $this->payment->emi_month,
            'interest' => $this->payment->interest,
            'created_by_name' => $this->payment->created_by_name,
            'updated_by_name' => $this->payment->updated_by_name,
            'created_at' => $this->payment->created_at,
            'updated_at' => $this->payment->updated_at,
            'deleted_at' => $this->payment->deleted_at,
        ];
    }

}
