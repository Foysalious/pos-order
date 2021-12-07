<?php namespace App\Services\OrderLog\Objects;


use App\Models\Order;
use JsonSerializable;

class OrderObject implements JsonSerializable
{
    private ?int $id;
    private ?int $partner_wise_order_id;
    private ?int $partner_id;
    private ?string $customer_id;
    private ?string $status;
    private ?int $sales_channel_id;
    private ?string $invoice;
    private ?int $emi_month;
    private ?int $interest;
    private ?float $delivery_charge;
    private ?string $delivery_request_id;
    private ?string $delivery_thana;
    private ?string $delivery_district;
    private ?float $bank_transaction_charge;
    private ?string $delivery_name;
    private ?string $delivery_mobile;
    private ?string $delivery_address;
    private ?string $note;
    private ?int $voucher_id;
    private ?string $paid_at;
    private ?int $api_request_id;
    private ?string $deleted_at;
    private ?string $created_by_name;
    private ?string $updated_by_name;
    private ?string $created_at;
    private ?string $updated_at;

    private Order $order;
    private $delivery_vendor;


    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param mixed $id
     * @return OrderObject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $partner_wise_order_id
     * @return OrderObject
     */
    public function setPartnerWiseOrderId($partner_wise_order_id)
    {
        $this->partner_wise_order_id = $partner_wise_order_id;
        return $this;
    }

    /**
     * @param mixed $partner_id
     * @return OrderObject
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param mixed $customer_id
     * @return OrderObject
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param mixed $status
     * @return OrderObject
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param mixed $sales_channel_id
     * @return OrderObject
     */
    public function setSalesChannelId($sales_channel_id)
    {
        $this->sales_channel_id = $sales_channel_id;
        return $this;
    }

    /**
     * @param mixed $invoice
     * @return OrderObject
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @param mixed $emi_month
     * @return OrderObject
     */
    public function setEmiMonth($emi_month)
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return OrderObject
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $delivery_charge
     * @return OrderObject
     */
    public function setDeliveryCharge($delivery_charge)
    {
        $this->delivery_charge = $delivery_charge;
        return $this;
    }

    /**
     * @param $delivery_vendor
     * @return OrderObject
     */
    public function setDeliveryVendor($delivery_vendor)
    {
        $this->delivery_vendor = $delivery_vendor;
        return $this;
    }

    /**
     * @param mixed $delivery_request_id
     * @return OrderObject
     */
    public function setDeliveryRequestId($delivery_request_id)
    {
        $this->delivery_request_id = $delivery_request_id;
        return $this;
    }

    /**
     * @param mixed $delivery_thana
     * @return OrderObject
     */
    public function setDeliveryThana($delivery_thana)
    {
        $this->delivery_thana = $delivery_thana;
        return $this;
    }

    /**
     * @param mixed $delivery_district
     * @return OrderObject
     */
    public function setDeliveryDistrict($delivery_district)
    {
        $this->delivery_district = $delivery_district;
        return $this;
    }

    /**
     * @param mixed $bank_transaction_charge
     * @return OrderObject
     */
    public function setBankTransactionCharge($bank_transaction_charge)
    {
        $this->bank_transaction_charge = $bank_transaction_charge;
        return $this;
    }

    /**
     * @param mixed $delivery_name
     * @return OrderObject
     */
    public function setDeliveryName($delivery_name)
    {
        $this->delivery_name = $delivery_name;
        return $this;
    }

    /**
     * @param mixed $delivery_mobile
     * @return OrderObject
     */
    public function setDeliveryMobile($delivery_mobile)
    {
        $this->delivery_mobile = $delivery_mobile;
        return $this;
    }

    /**
     * @param mixed $delivery_address
     * @return OrderObject
     */
    public function setDeliveryAddress($delivery_address)
    {
        $this->delivery_address = $delivery_address;
        return $this;
    }

    /**
     * @param mixed $note
     * @return OrderObject
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param mixed $voucher_id
     * @return OrderObject
     */
    public function setVoucherId($voucher_id)
    {
        $this->voucher_id = $voucher_id;
        return $this;
    }

    /**
     * @param mixed $paid_at
     * @return OrderObject
     */
    public function setPaidAt($paid_at)
    {
        $this->paid_at = $paid_at;
        return $this;
    }

    /**
     * @param mixed $api_request_id
     * @return OrderObject
     */
    public function setApiRequestId($api_request_id)
    {
        $this->api_request_id = $api_request_id;
        return $this;
    }

    /**
     * @param mixed $deleted_at
     * @return OrderObject
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    /**
     * @param mixed $created_by_name
     * @return OrderObject
     */
    public function setCreatedByName($created_by_name)
    {
        $this->created_by_name = $created_by_name;
        return $this;
    }

    /**
     * @param mixed $updated_by_name
     * @return OrderObject
     */
    public function setUpdatedByName($updated_by_name)
    {
        $this->updated_by_name = $updated_by_name;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return OrderObject
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @param mixed $updated_at
     * @return OrderObject
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
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
            'delivery_vendor' => $this->order->delivery_vendor,
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
            'customer' => $this->order->customer_id ? $this->getCustomer() : null,
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
            $discountObject->setOrderDiscount($discount);
            $discounts->push($discountObject);
        }
        return $discounts;
    }
}
