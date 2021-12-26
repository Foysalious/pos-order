<?php namespace App\Http\Resources;

use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\PriceCalculation;
use App\Services\Transaction\Constants\TransactionTypes;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class CustomerOrderResourceForPos extends JsonResource
{

    protected array $delivery_info;

    public function __construct($resource,$delivery_info = [])
    {
        $this->delivery_info = $delivery_info;
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $price_info = (object) $this->getOrderPriceRelatedInfo();
        return [
            'id' => $this->id,
            'previous_order_id' => $this->previous_order_id,
            'partner_wise_order_id' => $this->partner_wise_order_id,
            'note' => $this->note,
            'order_status' => $this->status,
            'payment_status' => $this->paid_at ? PaymentStatuses::PAID : PaymentStatuses::DUE,
            'sales_channel' => $this->sales_channel_id == SalesChannelIds::POS ? 'pos' : 'webstore',
            'created_by_name' => $this->created_by_name,
            'created_at' => convertTimezone($this->created_at)?->format('Y-m-d h:m A'),
            'date' => convertTimezone($this->created_at)?->format('Y-m-d'),
            'vat' => $price_info->vat,
            'paid' => $price_info->paid,
            'status' => $this->paid_at ? PaymentStatuses::PAID : PaymentStatuses::DUE,
            'due' => $price_info->due,
            'delivery_charge' => $price_info->delivery_charge,
            'address' => $this->delivery_address,
            'total_weight' => $this->getWeight(),
            'is_refundable' => false,
            'refund_status' => null,
            'return_orders' => [],
            'selected_delivery_method' => $this->delivery_info['delivery_method'] ?? null,
            'delivery_by_third_party' => $this->delivery_info['is_registered_for_sdelivery'] ? 1 : 0,
            'original_price' => $price_info->original_price,
            'price' => $price_info->discounted_price,
            'discount_amount' => $price_info->discount_amount,
            'items' => $this->formatOrderSkus(),
            'customer' => $this->getOrderCustomer(),
            'payments' => $this->getPayments(),
        ];
    }

    /**
     * @return array
     */
    private function getOrderPriceRelatedInfo(): array
    {
        /** @var PriceCalculation $price_calculator */
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($this->resource);

        return [

            'original_price' => (float) formatTakaToDecimal($price_calculator->getOriginalPrice()),
            'discounted_price_without_vat' => (float) formatTakaToDecimal($price_calculator->getDiscountedPriceWithoutVat()),
            'product_discount' => (float) formatTakaToDecimal($price_calculator->getProductDiscount()),
            'promo_discount' => (float) formatTakaToDecimal($price_calculator->getPromoDiscount()),
            'order_discount' => (float) formatTakaToDecimal($price_calculator->getOrderDiscount()),
            'vat' => (float) formatTakaToDecimal($price_calculator->getVat()),
            'delivery_charge' => (float) formatTakaToDecimal($price_calculator->getDeliveryCharge()),
            'discounted_price' => (float) formatTakaToDecimal($price_calculator->getDiscountedPrice()),
            'paid' => (float) formatTakaToDecimal($price_calculator->getPaid()),
            'due' => (float) formatTakaToDecimal($price_calculator->getDue()),
            'discount_amount' => (float) formatTakaToDecimal($price_calculator->getDiscount())
        ];
    }

    private function getPayments()
    {
        /** @var Collection $payments */
        $payments = $this->payments->where('transaction_type', TransactionTypes::CREDIT)->sortByDesc('created_at')->values();
        return $payments->map(function ($each) {
            $details = $each->method_details ? json_decode($each->method_details) : null;
            return [
                'amount' => $each->amount,
                'method' => $each->method,
                'method_en' =>  $details ? $details->payment_method_en : null,
                'method_bn' => $details ? $details->payment_method_bn : null,
                'method_icon' => $details ? $details->payment_method_icon : null,
                'created_at' => convertTimezone($each->created_at)?->format('Y-m-d h:m A'),
            ];
        });
    }

    private function getOrderCustomer()
    {
        if (empty($this->customer)) {
            return null;
        } else {
            return [
                'id' => $this->customer->id,
                'name' => $this->delivery_name ?? $this->customer->name,
                'mobile' => $this->delivery_mobile ?? $this->customer->mobile,
                'pro_pic' => $this->customer->pro_pic,
                'address' => $this->delivery_address,
            ];
        }
    }

    public function formatOrderSkus()
    {
        $items = [];
        $this->resource->orderSkus->each(function ($order_sku) use (&$items) {
            $order_sku = new OrderSkuResource($order_sku);
            $order_sku = (object) $order_sku->toArray(null);
            $items [] = [
                'id' => $order_sku->id,
                'item_id' => $order_sku->sku_id,
                'name' => $order_sku->name,
                'quantity' => $order_sku->quantity,
                'unit_price' => $order_sku->unit_original_price,
                'price' => $order_sku->unit_original_price,
                'price_without_vat' => $order_sku->unit_discounted_price_without_vat,
                'discount_amount' => $order_sku->unit_discount,
                'vat_percentage' => $order_sku->vat_percentage,
                'warranty' => $order_sku->warranty,
                'warranty_unit' => $order_sku->warranty_unit,
                'app_thumb' => $order_sku->app_thumb,
                'image_gallery' => []
            ];
        });
        return $items;
    }
}
