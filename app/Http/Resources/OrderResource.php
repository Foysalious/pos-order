<?php namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Order $this */
        return [
            'id'                      => $this->id,
            'partner_wise_order_id'   => $this->partner_wise_order_id,
            'customer_id'             => $this->customer_id,
            'status'                  => $this->status,
            'sales_channel_id'        => $this->sales_channel_id,
            'emi_month'               => $this->emi_month,
            'interest'                => $this->interest,
            'delivery_charge'         => $this->delivery_charge,
            'bank_transaction_charge' => $this->bank_transaction_charge,
            'delivery_name'           => $this->delivery_name,
            'delivery_mobile'         => $this->delivery_mobile,
            'delivery_address'        => $this->delivery_address,
            'note'                    => $this->note,
            'voucher_id'              => $this->voucher_id,
            'payment_status'          => $this->closed_and_paid_at,
            'order_update_message'    => $this->isUpdated() ? trans('order.update.updated') : null,
            'price'                   => $this->totalPrice(),
            'paid'                    => $this->paid(),
            'due'                     => $this->due()
        ];
    }
}
