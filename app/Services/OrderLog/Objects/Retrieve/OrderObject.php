<?php namespace App\Services\OrderLog\Objects\Retrieve;


class OrderObject
{
    private $id;
    private $partner_wise_order_id;
    private $partner_id;
    private $customer_id;
    private $status;
    private $sales_channel_id;
    private $invoice;
    private $emi_month;
    private $interest;
    private $delivery_charge;
    private $delivery_vendor_name;
    private $delivery_request_id;
    private $delivery_thana;
    private $delivery_district;
    private $bank_transaction_charge;
    private $delivery_name;
    private $delivery_mobile;
    private $delivery_address;
    private $note;
    private $voucher_id;
    private $paid_at;
    private $api_request_id;
    private $deleted_at;
    private $created_by_name;
    private $updated_by_name;
    private $created_at;
    private $updated_at;

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
     * @param mixed $delivery_vendor_name
     * @return OrderObject
     */
    public function setDeliveryVendorName($delivery_vendor_name)
    {
        $this->delivery_vendor_name = $delivery_vendor_name;
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
}
