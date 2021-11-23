<?php namespace App\Services\OrderLog\Objects\Retrieve;


use Illuminate\Support\Collection;

class OrderObjectRetriever
{
    private $order;

    /**
     * @param $order
     * @return OrderObjectRetriever
     */
    public function setOrder($order)
    {
        $this->order = is_object($order) ? $order : json_decode($order);
        return $this;
    }

    public function get()
    {
        if(!$this->order) return null;
        $order = $this->buildOrderObject();
        $order->items = $this->buildItemsObject();
        $order->orderSkus = $this->buildItemsObject();
        $order->customer = $this->buildCustomerObject();
        $order->payments = $this->buildPaymentsObject();
        $order->discounts = $this->buildDiscountsObject();
        return $order;

    }

    private function buildOrderObject()
    {
        /** @var OrderObject $orderObject */
        $orderObject = app(OrderObject::class);
        $orderObject->setId($this->order->id)
            ->setPartnerWiseOrderId($this->order->partner_wise_order_id)
            ->setPartnerId($this->order->partner_id)->setCustomerId($this->order->customer_id)
            ->setStatus($this->order->status)->setSalesChannelId($this->order->sales_channel_id)
            ->setInvoice($this->order->invoice)->setEmiMonth($this->order->emi_month)->setInterest($this->order->interest)
            ->setDeliveryCharge($this->order->delivery_charge)->setDeliveryVendorName($this->order->delivery_vendor_name)
            ->setDeliveryRequestId($this->order->delivery_request_id)->setDeliveryThana($this->order->delivery_thana)
            ->setDeliveryDistrict($this->order->delivery_district)->setBankTransactionCharge($this->order->bank_transaction_charge)
            ->setDeliveryName($this->order->delivery_name)->setDeliveryMobile($this->order->delivery_mobile)
            ->setDeliveryAddress($this->order->delivery_address)->setNote($this->order->note)->setVoucherId($this->order->voucher_id)
            ->setPaidAt($this->order->voucher_id)->setApiRequestId($this->order->api_request_id)->setDeletedAt($this->order->deleted_at)
            ->setCreatedByName($this->order->created_by_name)->setUpdatedByName($this->order->updated_by_name)
            ->setCreatedAt($this->order->created_at)->setUpdatedAt($this->order->updated_at);
        return $orderObject;
    }

    private function buildItemsObject(): Collection
    {
        $items = collect();
        foreach ($this->order->items as $item){
            /** @var ItemObject $itemObject */
            $itemObject = app(ItemObject::class);
            $itemObject->setId($item->id)->setOrderId($item->order_id)->setName($item->name)->setSkuId($item->sku_id)
                ->setDetails($item->details)->setQuantity($item->quantity)->setUnitWeight($item->unit_weight)
                ->setUnitPrice($item->unit_price)->setBatchDetail($item->batch_detail)
                ->setIsEmiAvailable($item->is_emi_available)->setUnit($item->unit)->setVatPercentage($item->vat_percentage)
                ->setWarranty($item->warranty)->setWarrantyUnit($item->warranty_unit)->setNote($item->note)
                ->setProductImage($item->product_image)->setCreatedByName($item->created_by_name)
                ->setUpdatedByName($item->updated_by_name)->setCreatedAt($item->created_at)->setUpdatedAt($item->updated_at)
                ->setDeletedAt($item->deleted_at)->setDiscount($item->discount);
            $items->push($itemObject);
        }
        return $items;
    }

    private function buildPaymentsObject()
    {
        $payments = collect();
        foreach ($this->order->payments as $payment){
            /** @var PaymentObject $paymentObject */
            $paymentObject = app(PaymentObject::class);
            $paymentObject->setId($payment->id)->setOrderId($payment->order_id)->setAmount($payment->amount)
                ->setTransactionType($payment->transaction_type)->setMethod($payment->method)
                ->setMethodDetails($payment->method_details)->setEmiMonth($payment->emi_month)
                ->setInterest($payment->interest)->setCreatedByName($payment->created_by_name)
                ->setUpdatedByName($payment->updated_by_name)->setCreatedAt($payment->created_at)
                ->setUpdatedAt($payment->updated_at)->setDeletedAt($payment->deleted_at);
            $payments->push($paymentObject);
        }
        return $payments;
    }

    private function buildCustomerObject()
    {
        $customer = $this->order->customer;
        /** @var CustomerObject $customerObject */
        $customerObject = app(CustomerObject::class);
        $customerObject->setId($customer->id)->setName($customer->name)->setIsSupplier($customer->is_supplier)
        ->setEmail($customer->email)->setMobile($customer->mobile)->setProPic($customer->pro_pic)
        ->setDeletedAt($customer->deleted_at)->setCreatedAt($customer->created_at)->setUpdatedAt($customer->updated_at)
        ->setCreatedByName($customer->created_by_name)->setUpdatedByName($customer->updated_by_name);
        return $customerObject;
    }

    private function buildDiscountsObject()
    {
        $discounts = collect();
        foreach ($this->order->discounts as $discount){
            /** @var DiscountObject $discountObject */
            $discountObject = app(DiscountObject::class);
            $discountObject->setId($discount->id)->setOrderId($discount->order_id)->setType($discount->type)
                ->setAmount($discount->amount)->setOriginalAmount($discount->original_amount)
                ->setIsPercentage($discount->is_percentage)->setCap($discount->cap)->setDiscountDetails($discount->discount_details)
                ->setDiscountId($discount->discount_id)->setTypeId($discount->type_id)->setCreatedByName($discount->created_by_name)
                ->setUpdatedByName($discount->updated_by_name)->setCreatedAt($discount->created_at)->setUpdatedAt($discount->updated_at)
                ->setDeletedAt($discount->deleted_at);
            $discounts->push($discountObject);
        }
        return $discounts;
    }

    function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
