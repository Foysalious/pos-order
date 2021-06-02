<?php

namespace App\Http\Resources;

use App\Services\PaymentLink\PaymentLinkTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderWithProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return $this->getOrderDetailsWithoutPaymentLink();
    }

    public function getOrderDetailsWithoutPaymentLink(): array
    {
        return [
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
                'promo'             => $this->getVoucher()->pluck('amount')->first(),
            ],
            'customer_info'           => $this->customer->only('name','phone','pro_pic'),
            'payment_info'            => $this->payments,
        ];
    }

    public function getOrderDetailsWithPaymentLink(PaymentLinkTransformer $payment_link): array
    {
        $order_data = $this->getOrderDetailsWithoutPaymentLink();
        $order_data['payment_link'] = [
            'id' => $payment_link->getLinkID(),
            'status' => $payment_link->getIsActive() ? 'active' : 'inactive',
            'link' => $payment_link->getLink(),
            'amount' => $payment_link->getAmount(),
            'created_at' => $payment_link->getCreatedAt()->format('d-m-Y h:s A')
        ];
        return $order_data;
    }
}
