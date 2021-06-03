<?php

namespace App\Http\Resources;

use App\Services\Order\PriceCalculation;
use App\Services\PaymentLink\PaymentLinkTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class OrderWithProductResource extends JsonResource
{
    private $order;
    /**
     * OrderWithProductResource constructor.
     */
    public function __construct($order)
    {
        $this->order = $order;
        parent::__construct($order);
    }


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
            'price_info'              => $this->getOrderPriceRelatedInfo(),
            'customer_info'           => $this->customer->only('name','phone','pro_pic'),
            'payment_info'            => OrderPaymentResource::collection($this->payments),
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

    private function getOrderPriceRelatedInfo()
    {
        /** @var PriceCalculation $price_calculator */
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($this->order);

        return [
            'delivery_charge'   => $this->delivery_charge,
            'promo'             => $this->getVoucher()->pluck('amount')->first(),
            'total_price' => $price_calculator->getTotalPrice(),
            'total_bill' => $price_calculator->getTotalBill(),
            'discount_amount' => $price_calculator->getDiscountAmount(),
            'due_amount' => $price_calculator->getDue(),
            'paid_amount' => $price_calculator->getPaid(),
            'total_item_discount' => $price_calculator->getTotalItemDiscount(),
            'total_vat' => $price_calculator->getTotalVat(),
        ];
    }
}
