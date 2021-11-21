<?php namespace App\Services\OrderLog\Objects\Store;


use App\Models\Order;
use JsonSerializable;

class OrderObject implements JsonSerializable
{
    private Order $order;

    /**
     * @param Order $order
     * @return OrderObject
     */
    public function setOrder(Order $order): OrderObject
    {
        $this->order = $order;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->order->id,
            'partner_wise_order_id' => $this->order->partner_wise_order_id,
            'partner_id' => $this->order->partner_id,
            'customer_id' => $this->order->customer_id,
            'status' => $this->order->status,
            'sales_channel_id' => $this->order->sales_channel_id,
            'invoice' => $this->order->invoice,
            'emi_month' => $this->order->emi_month,
            'interest' => $this->order->interest,
            'delivery_charge' => $this->order->delivery_charge,
            'delivery_vendor_name' => $this->order->delivery_vendor_name,
            'delivery_request_id' => $this->order->delivery_request_id,
            'delivery_thana' => $this->order->delivery_thana,
            'delivery_district' => $this->order->delivery_district,
            'bank_transaction_charge' => $this->order->bank_transaction_charge,
            'delivery_name' => $this->order->delivery_name,
            'delivery_mobile' => $this->order->delivery_mobile,
            'delivery_address' => $this->order->delivery_address,
            'note' => $this->order->note,
            'voucher_id' => $this->order->voucher_id,
            'paid_at' => $this->order->paid_at,
            'api_request_id' => $this->order->api_request_id,
            'deleted_at' => $this->order->deleted_at,
            'created_by_name' => $this->order->created_by_name,
            'updated_by_name' => $this->order->updated_by_name,
            'created_at' => $this->order->created_at,
            'updated_at' => $this->order->updated_at,
            'items' => $this->getItems(),
            'customer' => $this->order->customer ? $this->getCustomer() : null,
            'payments' => $this->getPayments(),
            'discounts' => $this->getDiscounts()
        ];
    }

    private function getItems()
    {
        $items = collect();
        foreach ($this->order->items as $item){
            /** @var ItemObject $itemObject */
            $itemObject = app(ItemObject::class);
            $itemObject->setOrderSku($item);
            $items->push($itemObject);
        }
        return $items->toArray();
    }

    private function getPayments()
    {
        $payments = collect();
        foreach ($this->order->payments as $payment){
            /** @var PaymentObject $paymentObject */
            $paymentObject = app(PaymentObject::class);
            $paymentObject->setPayment($payment);
            $payments->push($paymentObject);
        }
        return $payments->toArray();
    }

    private function getCustomer()
    {
        /** @var CustomerObject $customerObject */
        $customerObject = app(CustomerObject::class);
        $customerObject->setCustomer($this->order->customer);
        return $customerObject;
    }

    private function getDiscounts()
    {
        $discounts = collect();
        foreach ($this->order->discounts as $discount) {
            /** @var DiscountObject $discountObject */
            $discountObject = app(DiscountObject::class);
            $discountObject->setDiscount($discount);
            $discounts->push($discountObject);
        }
        return $discounts;
    }
}
