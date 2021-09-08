<?php namespace App\Services\Order\Objects;


use App\Models\Order;

class OrderDetailsObject
{
    protected $orderDetails;
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
    /** @var OrderPaymentObject[]|null */
    protected ?array $payments;
    protected ?OrderPaymentLinkObject $payment_link;

    public function setOrderDetails(Order $orderDetail): OrderDetailsObject
    {
        $this->orderDetails = $orderDetail;
        return $this;
    }

    public function build(): OrderDetailsObject
    {
        $this->id = $this->orderDetails->id;
        $this->created_at = $this->orderDetails->created_at;
        $this->partner_wise_order_id = $this->orderDetails->partner_wise_order_id;
        $this->status = $this->orderDetails->status;
        $this->sales_channel_id = $this->orderDetails->sales_channel_id;
        $this->sales_channel_id = $this->orderDetails->sales_channel_id;
        $this->delivery_name = $this->orderDetails->delivery_name;
        $this->note = $this->orderDetails->note;
        $this->buildSkus();
        $this->buildPrice();
        $this->buildCustomer();
        $this->buildPayments();
        $this->buildPaymentLink();
        return $this;
    }

    private function buildSkus(): void
    {
        $final = [];
        foreach ($this->orderDetails->orderSkus as $sku) {
            /** @var OrderSkuObject $orderSkuObject */
            $orderSkuObject = app(OrderSkuObject::class);
            array_push($final, $orderSkuObject->setSkuDetails($sku)->build());
        }
        $this->skus = $final;
    }

    private function buildPrice(): void
    {
        /** @var OrderPriceObject $orderPriceObject */
        $orderPriceObject = app(OrderPriceObject::class);
        dd($this->orderDetails);
        $price = $orderPriceObject->setPriceDetails($this->orderDetails->price)->build();
        $this->price = $price;
    }

    private function buildCustomer(): void
    {
        /** @var OrderCustomerObject $orderCustomerObject */
        $orderCustomerObject = app(OrderCustomerObject::class);
        $customer = $orderCustomerObject->setCustomerDetails($this->orderDetails->customer)->build();
        $this->customer = $customer;
    }

    private function buildPayments(): void
    {
        $final = [];
        foreach ($this->orderDetails->paymnets as $payment) {
            /** @var OrderPaymentObject $orderPaymentObject */
            $orderPaymentObject = app(OrderPaymentObject::class);
            array_push($final, $orderPaymentObject->setPaymentDetails($payment)->build());
        }
        $this->payments = $final;
    }

    private function buildPaymentLink(): void
    {
        /** @var OrderPaymentLinkObject $orderPaymentLinkObject */
        $orderPaymentLinkObject = app(OrderPaymentLinkObject::class);
        $payment_link = $orderPaymentLinkObject->setPaymentLinkDetails($this->orderDetails->payment_link)->build();
        $this->payment_link = $payment_link;
    }
}
