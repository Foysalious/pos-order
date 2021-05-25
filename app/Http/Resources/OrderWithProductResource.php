<?php

namespace App\Http\Resources;

use App\Services\PaymentLink\Constants\TargetType;
use App\Services\PaymentLink\Target;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderWithProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id'                      => $this->id,
            'previous_order_id'       => $this->previous_order_id,
            'partner_wise_order_id'   => $this->partner_wise_order_id,
            'customer_id'             => $this->customer_id,
            'status'                  => $this->status,
            'sales_channel_id'        => $this->sales_channel_id,
            'emi_month'               => $this->emi_month,
            'interest'                => $this->interest,
            'bank_transaction_charge' => $this->bank_transaction_charge,
            'delivery_name'           => $this->delivery_name,
            'delivery_mobile'         => $this->delivery_mobile,
            'delivery_address'        => $this->delivery_address,
            'note'                    => $this->note,
            'voucher_id'              => $this->voucher_id,
            'items'                   => OrderSkuResource::collection($this->items),
            'price_info'              => [
                'delivery_charge'   =>  $this->delivery_charge,
                'total_price'       => $this->totalPrice,
                'total_vat'         => $this->totalVat,
                'total_bill'        => $this->totalBill,
                'totalDiscount'     => $this->totalDiscount,
                'due'               => $this->due,
            ],
            'customer_info'           => $this->customer->only('name','phone','image'),
            'payment_info'            => $this->payments,
        ];

        return $data;
    }
}
