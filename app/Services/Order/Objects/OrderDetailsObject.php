<?php namespace App\Services\Order\Objects;


use App\Models\Order;
use App\Services\Order\PriceCalculation;
use stdClass;

class OrderDetailsObject
{
    protected $orderDetails;
    /** @var OrderSkuObject[]|null  */
    protected ?array $skus;
    protected ?OrderPriceObject $price;
    protected ?OrderCustomerObject $customer;
    /** @var OrderPaymentObject[]|null */
    protected ?array $payments;
    protected ?OrderPaymentLinkObject $payment_link;
    private OrderObject $order;

    public function setOrderDetails($orderDetail): OrderDetailsObject
    {
        $this->orderDetails = $orderDetail;
        return $this;
    }

    public function get()
    {
        $this->build();
        return $this->order;
    }

    public function toArray()
    {
        $this->build();
        dd($this->order->skus->toArray());
    }

    private function build()
    {
        $this->buildOrderDetails();
        $this->buildSkus();
        $this->buildPrice();
        $this->buildCustomer();
        $this->buildPayments();
        $this->buildPaymentLink();
    }

    private function buildOrderDetails()
    {
        /** @var OrderObject $orderObject */
        $orderObject = app(OrderObject::class);
        $order = $orderObject->setId($this->orderDetails->id)->setCreatedAt($this->orderDetails->created_at)
            ->setPartnerWiseOrderId($this->orderDetails->partner_wise_order_id)
            ->setStatus($this->orderDetails->status)->setSalesChannelId($this->orderDetails->sales_channel_id)
            /**->setDeliveryName($this->orderDetails->delivery_name)
            ->setDeliveryMobile($this->orderDetails->delivery_mobile)
            ->setDeliveryAddress($this->orderDetails->delivery_address)**/
            ->setNote($this->orderDetails->note);
        $this->order = $order;
    }

    private function buildSkus(): void
    {
        $final = [];

        foreach ($this->orderDetails->items as $sku) {
//            dd($sku);
            /** @var OrderSkuObject $orderSkuObject */
            $orderSkuObject = app(OrderSkuObject::class);
            array_push($final, $orderSkuObject->setId($sku->id)->setName($sku->name)/**->setProductId($sku->product_id)
            ->setProductName($sku->product_name)**/->setNote($sku->note)->setUnit($sku->unit)->setSkuId($sku->sku_id)
            ->setOriginalPrice($sku->original_price)->setQuantity($sku->quantity)->setBatchDetail($sku->batch_detail)
            ->setDiscount($sku->discount)->setIsDiscountPercentage($sku->is_discount_percentage)->setCap($sku->cap)
            ->setVatPercentage($sku->vat_percentage)->setWarranty($sku->warranty)->setWarrantyUnit($sku->warranty_unit)->setSkuChannelId($sku->sku_channel_id)
            ->setChannelId($sku->channel_id)->setChannelName($sku->channel_name)->setCombination($sku->combination));
        }
        $this->order->skus = $final;
    }

    private function buildPrice(): void
    {
        /** @var OrderPriceObject $orderPriceObject */
        $orderPriceObject = app(OrderPriceObject::class);
        $priceDetails = new stdClass();
        /** @var PriceCalculation $priceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        $priceCalculation->setOrder($this->orderDetails);
        $priceDetails->original_price = $priceCalculation->getDiscountedPrice();
        $priceDetails->discounted_price_without_vat = $priceCalculation->getDiscountedPriceWithoutVat();
        $priceDetails->promo_discount = $priceCalculation->getPromoDiscount();
        $priceDetails->order_discount = $priceCalculation->getOrderDiscount();
        $priceDetails->vat = $priceCalculation->getVat();
        $priceDetails->delivery_charge = $priceCalculation->getDeliveryCharge();
        $priceDetails->discounted_price = $priceCalculation->getDiscountedPrice();
        $priceDetails->paid = $priceCalculation->getPaid();
        $priceDetails->due = $priceCalculation->getDue();
        $price = $orderPriceObject->setOriginalPrice($priceCalculation->getDiscountedPrice())
            ->setDiscountedPriceWithoutVat($priceCalculation->getDiscountedPriceWithoutVat())
            ->setPromoDiscount($priceCalculation->getPromoDiscount())
            ->setOrderDiscount($priceCalculation->getOrderDiscount())
            ->setVat($priceCalculation->getVat())
            ->setDeliveryCharge($priceCalculation->getDeliveryCharge())
            ->setDiscountedPrice($priceCalculation->getDeliveryCharge())
            ->setPaid($priceCalculation->getPaid())
            ->setDue($priceCalculation->getDue());
        $this->order->price = $price;
    }

    private function buildCustomer(): void
    {
        /** @var OrderCustomerObject $orderCustomerObject */
        $orderCustomerObject = app(OrderCustomerObject::class);
        $customer = $orderCustomerObject->setName($this->orderDetails?->customer?->name)
            ->setMobile($this->orderDetails?->customer?->mobile)
            ->setProPic($this->orderDetails?->customer?->pro_pic);
        $this->order->customer = $customer;
    }

    private function buildPayments(): void
    {
        $final = [];
        foreach ($this->orderDetails->payments as $payment) {
            /** @var OrderPaymentObject $orderPaymentObject */
            $orderPaymentObject = app(OrderPaymentObject::class);
            array_push($final, $orderPaymentObject->setAmount($payment->amount)->setMethod($payment->method)->setCreatedAt($payment->created_at));
        }
        $this->order->payments = $final;
    }

    private function buildPaymentLink(): void
    {
        /** @var OrderPaymentLinkObject $orderPaymentLinkObject */
        $orderPaymentLinkObject = app(OrderPaymentLinkObject::class);
        if ($this->orderDetails->payment_link) {
            $payment_link = $orderPaymentLinkObject->setId($this->orderDetails->payment_link->id)
                ->setAmount($this->orderDetails->payment_link->amount)
                ->setLink($this->orderDetails->payment_link->link)
                ->setStatus($this->orderDetails->payment_link->status)
                ->setCreatedAt($this->orderDetails->payment_link->created_at);
        } else {
            $payment_link = null;
        }

        $this->order->payment_link = $payment_link;
    }
}
