<?php namespace App\Http\Resources;

use App\Models\Order;
use App\Repositories\PaymentLinkRepository;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\PriceCalculation;
use App\Services\PaymentLink\PaymentLinkTransformer;
use App\Services\Transaction\Constants\TransactionTypes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class OrderWithProductResource extends JsonResource
{
    private Order $order;
    private array $orderWithProductResource = [];

    /**
     * OrderWithProductResource constructor.
     */
    public function __construct($order)
    {
        parent::__construct($order);
        $this->order = $order;
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $this->orderWithProductResource = [
            'id' => $this->id,
            'created_at' => convertTimezone($this->created_at)->format('Y-m-d H:i:s'),
            'created_by_name' => $this->created_by_name,
            'previous_order_id' => $this->previous_order_id,
            'partner_wise_order_id' => $this->partner_wise_order_id,
            'status' => $this->status,
            'payment_status' => $this->paid_at ? PaymentStatuses::PAID : PaymentStatuses::DUE,
            'sales_channel_id' => $this->sales_channel_id,
            'note' => $this->note,
            'invoice' => $this->invoice,
            'items' => OrderSkuResource::collection($this->orderSkus),
            'price' => $this->getOrderPriceRelatedInfo(),
            'customer' => $this->getOrderCustomer(),
            'payments' => $this->getPayments(),
            'promo_code' => $this->getPromoCode(),
        ];
        $this->orderWithProductResource['payment_link'] = $this->getOrderDetailsWithPaymentLink();
        return $this->orderWithProductResource;
    }

    /**
     * @return array
     */
    private function getOrderPriceRelatedInfo(): array
    {
        /** @var PriceCalculation $price_calculator */
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($this->order);

        return [
            'original_price' => $price_calculator->getOriginalPrice(),
            'discounted_price_without_vat' => $price_calculator->getDiscountedPriceWithoutVat(),
            'product_discount' => $price_calculator->getProductDiscount(),
            'promo_discount' => $price_calculator->getPromoDiscount(),
            'order_discount' => $price_calculator->getOrderDiscount(),
            'vat' => $price_calculator->getVat(),
            'delivery_charge' => $price_calculator->getDeliveryCharge(),
            'discounted_price' => $price_calculator->getDiscountedPrice(),
            'paid' => $price_calculator->getPaid(),
            'due' => $price_calculator->getDue(),
        ];
    }

    /**
     * @return array|null
     */
    private function getOrderDetailsWithPaymentLink(): ?array
    {
        $payment_link = [];
        if (isset($this->orderWithProductResource['price']['due']) && $this->orderWithProductResource['price']['due'] > 0) {
            $payment_link_target = $this->order->getPaymentLinkTarget();
            /** @var PaymentLinkRepository $paymentLinkRepository */
            $paymentLinkRepository = App::make(PaymentLinkRepository::class);
            /** @var PaymentLinkTransformer $payment_link_transformer */
            $payment_link_transformer = $paymentLinkRepository->getActivePaymentLinkByPosOrder($payment_link_target);
            if ($payment_link_transformer) {
                $payment_link = [
                    'id' => $payment_link_transformer->getLinkID(),
                    'status' => $payment_link_transformer->getIsActive() ? 'active' : 'inactive',
                    'link' => $payment_link_transformer->getLink(),
                    'amount' => $payment_link_transformer->getAmount(),
                    'created_at' => $payment_link_transformer->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }
        }
        if (empty($payment_link)) return null;
        return $payment_link;
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
                'method_bn' => $details ? $details->payment_method_bn : null,
                'method_icon' => $details ? $details->payment_method_icon : null,
                'created_at' => convertTimezone($each->created_at)->format('Y-m-d H:i:s'),
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

    private function getPromoCode()
    {
        $voucher = $this->voucherDiscounts->first();
        if(!$voucher) return null;
        $details = json_decode($voucher->discount_details);
        return !is_null($details) ? $details->promo_code : null;
    }
}
