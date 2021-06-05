<?php

namespace App\Http\Resources;

use App\Repositories\PaymentLinkRepository;
use App\Services\Order\PriceCalculation;
use App\Services\PaymentLink\PaymentLinkTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class OrderWithProductResource extends JsonResource
{
    private $order;
    private array $orderWithProductResource = [];
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
        $this->orderWithProductResource = [
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
        ];
        $this->orderWithProductResource['payment_info'] = $this->getOrderDetailsWithPaymentLink();
        return $this->orderWithProductResource;
    }

    /**
     * @return array
     */
    private function getOrderPriceRelatedInfo() : array
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

    /**
     * @return array|null
     */
    private function getOrderDetailsWithPaymentLink(): ?array
    {
        $payment_info = null;
        if( isset($this->orderWithProductResource['price_info']['due_amount']) && $this->orderWithProductResource['price_info']['due_amount'] > 0){
            $payment_link_target = $this->order->getPaymentLinkTarget();
            /** @var PaymentLinkRepository $paymentLinkRepository */
            $paymentLinkRepository = App::make(PaymentLinkRepository::class);
            /** @var PaymentLinkTransformer $payment_link_transformer */
            $payment_link_transformer = $paymentLinkRepository->getActivePaymentLinkByPosOrder($payment_link_target);
            if ($payment_link_transformer) {
                $payment_info = [
                    'id' => $payment_link_transformer->getLinkID(),
                    'status' => $payment_link_transformer->getIsActive() ? 'active' : 'inactive',
                    'link' => $payment_link_transformer->getLink(),
                    'amount' => $payment_link_transformer->getAmount(),
                    'created_at' => $payment_link_transformer->getCreatedAt()->format('d-m-Y h:s A')
                ];
            }
        }
        return $payment_info;
    }
}
