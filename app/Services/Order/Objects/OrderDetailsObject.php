<?php namespace App\Services\Order\Objects;


class OrderDetailsObject
{
    protected $orderDetail;
    protected $id;
    protected $created_at;
    protected $partner_wise_order_id;
    protected $status;
    protected $sales_channel_id;
    protected $delivery_name;
    protected $delivery_mobile;
    protected $delivery_address;
    protected $note;
    /** @var OrderSkuObject[]|null  */
    protected ?array $skus;
    protected ?OrderPriceObject $price;
    protected ?OrderCustomerObject $customer;
    /** @var OrderPaymentObject[]|null  */
    protected ?array $payments;
    protected ?OrderPaymentLinkObject $payment_link;

    public function setOrderId($orderDetail)
    {
        $this->orderDetail = $orderDetail;
        return $this;
    }

    public function build()
    {
        $this->id = $this->orderDetail->id;
        $this->created_at = $this->orderDetail->created_at;
        $this->partner_wise_order_id = $this->orderDetail->partner_wise_order_id;
        $this->status = $this->orderDetail->status;
        $this->sales_channel_id = $this->orderDetail->sales_channel_id;
        $this->sales_channel_id = $this->orderDetail->sales_channel_id;
        $this->delivery_name = $this->orderDetail->delivery_name;
        $this->note = $this->orderDetail->note;
        $this->buildSkus();
        $this->buildPrice();
        $this->buildCustomer();
        $this->buildPayments();
        $this->buildPaymentLink();
        return $this;
    }

    private function buildSkus()
    {
        $final = [];
        foreach ($this->orderDetail->skus as $sku) {
            /** @var OrderSkuObject $orderSkuObject */
            $orderSkuObject = app(OrderSkuObject::class);
            array_push($final, $orderSkuObject->setSkuDetails($sku)->build());
        }
        $this->skus = $final;
        return $this;
    }

    private function buildPrice()
    {
        /** @var OrderPriceObject $orderPriceObject */
        $orderPriceObject = app(OrderPriceObject::class);
        $price = $orderPriceObject->setPriceDetails($this->orderDetail->price)->build();
        $this->price = $price;
        return $this;
    }

    private function buildCustomer()
    {
        /** @var OrderCustomerObject $orderCustomerObject */
        $orderCustomerObject = app(OrderCustomerObject::class);
        $customer = $orderCustomerObject->setCustomerDetails($this->orderDetail->customer)->build();
        $this->customer = $customer;
        return $this;
    }

    private function buildPayments()
    {
        $final = [];
        foreach ($this->orderDetail->paymnets as $payment) {
            /** @var OrderPaymentObject $orderPaymentObject */
            $orderPaymentObject = app(OrderPaymentObject::class);
            array_push($final, $orderPaymentObject->setPaymentDetails($payment)->build());
        }
        $this->payments = $final;
        return $this;
    }

    private function buildPaymentLink()
    {
        /** @var OrderPaymentLinkObject $orderPaymentLinkObject */
        $orderPaymentLinkObject = app(OrderPaymentLinkObject::class);
        $payment_link = $orderPaymentLinkObject->setPaymentLinkDetails($this->orderDetail->payment_link)->build();
        $this->payment_link = $payment_link;
        return $this;
    }
}
