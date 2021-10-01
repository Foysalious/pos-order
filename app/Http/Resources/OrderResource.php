<?php namespace App\Http\Resources;

use App\Models\Order;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\PriceCalculation;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Order $this */
        /** @var PriceCalculation $price_calculator */
        $price_calculator = app(PriceCalculation::class)->setOrder($this->resource);
        return [
            'id' => $this->id,
            'partner_wise_order_id' => $this->partner_wise_order_id,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'sales_channel_id' => $this->sales_channel_id,
            'delivery_name' => $this->delivery_name,
            'delivery_mobile' => $this->delivery_mobile,
            'delivery_address' => $this->delivery_address,
            'note' => $this->note,
            'voucher_id' => $this->voucher_id,
            'payment_status' => $this->paid_at ? PaymentStatuses::PAID : PaymentStatuses::DUE,
            'order_update_message' => $this->isUpdated() ? trans('order.update.updated') : null,
            'original_price' => $price_calculator->getOriginalPrice(),
            'discounted_price_without_vat' => $price_calculator->getDiscountedPriceWithoutVat(),
            'promo_discount' => $price_calculator->getPromoDiscount(),
            'order_discount' => $price_calculator->getOrderDiscount(),
            'vat' => $price_calculator->getVat(),
            'delivery_charge' => $price_calculator->getDeliveryCharge(),
            'discounted_price' => $price_calculator->getDiscountedPrice(),
            'paid' => $price_calculator->getPaid(),
            'due' => $price_calculator->getDue(),
            'created_at' => convertTimezone($this->created_at)->format('Y-m-d H:i:s')
        ];
    }
}
