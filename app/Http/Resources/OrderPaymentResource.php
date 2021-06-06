<?php namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderPaymentResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'transaction_type' => $this->transaction_type,
            'method' => $this->method,
            'emi_month' => $this->emi_month,
            'interest' => $this->interest,
        ];
    }
}
