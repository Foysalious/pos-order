<?php


namespace App\Services\Order;


use App\Interfaces\OrderRepositoryInterface;

class Updater
{
    protected $partner_id, $order_id, $customer_id, $status, $sales_channel_id, $emi_month, $interest, $delivery_charge;
    protected $bank_transaction_charge, $delivery_name, $delivery_mobile, $delivery_address, $note, $voucher_id;
    protected $orderItems;

    protected $orderRepositoryInterface;

    public function __construct(OrderRepositoryInterface $orderRepositoryInterface)
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
    }

    /**
     * @param mixed $orderItems
     * @return Updater
     */
    public function setOrderItems($orderItems)
    {
        $this->orderItems = $orderItems;
        return $this;
    }

    /**
     * @param mixed $voucher_id
     * @return Updater
     */
    public function setVoucherId($voucher_id)
    {
        $this->voucher_id = $voucher_id;
        return $this;
    }

    /**
     * @param mixed $note
     * @return Updater
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param mixed $delivery_address
     * @return Updater
     */
    public function setDeliveryAddress($delivery_address)
    {
        $this->delivery_address = $delivery_address;
        return $this;
    }

    /**
     * @param mixed $delivery_mobile
     * @return Updater
     */
    public function setDeliveryMobile($delivery_mobile)
    {
        $this->delivery_mobile = $delivery_mobile;
        return $this;
    }

    /**
     * @param mixed $delivery_name
     * @return Updater
     */
    public function setDeliveryName($delivery_name)
    {
        $this->delivery_name = $delivery_name;
        return $this;
    }

    /**
     * @param mixed $bank_transaction_charge
     * @return Updater
     */
    public function setBankTransactionCharge($bank_transaction_charge)
    {
        $this->bank_transaction_charge = $bank_transaction_charge;
        return $this;
    }

    /**
     * @param mixed $delivery_charge
     * @return Updater
     */
    public function setDeliveryCharge($delivery_charge)
    {
        $this->delivery_charge = $delivery_charge;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return Updater
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $emi_month
     * @return Updater
     */
    public function setEmiMonth($emi_month)
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @param mixed $sales_channel_id
     * @return Updater
     */
    public function setSalesChannelId($sales_channel_id)
    {
        $this->sales_channel_id = $sales_channel_id;
        return $this;
    }

    /**
     * @param mixed $status
     * @return Updater
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param mixed $customer_id
     * @return Updater
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param mixed $order_id
     * @return Updater
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param mixed $partner_id
     * @return Updater
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    public function update()
    {
        $orderModel = $this->orderRepositoryInterface->findOrFail($this->order_id);
        $this->orderRepositoryInterface->update($orderModel, $this->makeData());
    }

    protected function makeData()
    {
        $data = [];
        //if(isset($this->))
        return $data;
    }
}
