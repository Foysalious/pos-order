<?php namespace App\Services\OrderLog\Objects\Store;


use App\Models\OrderPayment;
use JsonSerializable;

class PaymentObject implements JsonSerializable
{
    private OrderPayment $payment;
    /**
     * @param OrderPayment $payment
     * @return PaymentObject
     */
    public function setPayment(OrderPayment $payment): PaymentObject
    {
        $this->payment = $payment;
        return $this;
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
