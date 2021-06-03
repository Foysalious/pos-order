<?php namespace App\Services\Order;

use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Services\Order\Constants\OrderLogTypes;
use App\Traits\ModificationFields;

class Updater
{
    use ModificationFields;
    protected $partner_id, $order_id, $customer_id, $status, $sales_channel_id, $emi_month, $interest, $delivery_charge;
    protected $bank_transaction_charge, $delivery_name, $delivery_mobile, $delivery_address, $note, $voucher_id;
    protected $skus, $order, $existingOrder;
    protected $orderLogCreator;
    protected $orderRepositoryInterface, $orderSkusRepositoryInterface, $orderPaymentRepository;
    protected string $orderLogType = OrderLogTypes::OTHERS;

    public function __construct(OrderRepositoryInterface $orderRepositoryInterface,
                                OrderSkusRepositoryInterface $orderSkusRepositoryInterface,
                                OrderLogCreator $orderLogCreator,
                                OrderPaymentRepositoryInterface $orderPaymentRepository)
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->orderLogCreator = $orderLogCreator;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * @param string $orderLogType
     * @return Updater
     */
    public function setOrderLogType(string $orderLogType): Updater
    {
        $this->orderLogType = $orderLogType;
        return $this;
    }

    /**
     * @param mixed $order
     * @return Updater
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param mixed $updatedSkus
     * @return Updater
     */
    public function setUpdatedSkus($updatedSkus)
    {
        $this->skus = $updatedSkus;
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
        //$this->skus ? $this->orderSkusRepositoryInterface->updateOrderSkus($this->partner_id, json_decode($this->skus), $this->order_id) : null;
        list($previous_order, $existing_order_skus) = $this->setExistingOrderAndSkus();
        $this->orderRepositoryInterface->update($this->order, $this->makeData());
        $this->createLog($previous_order, $existing_order_skus);
    }

    public function makeData() : array
    {
        $data = [];
        if(isset($this->customer_id)) $data['customer_id']                          = $this->customer_id;
        if(isset($this->sales_channel_id)) $data['sales_channel_id']                = $this->sales_channel_id;
        if(isset($this->emi_month)) $data['emi_month']                              = $this->emi_month;
        if(isset($this->interest)) $data['interest']                                = $this->interest;
        if(isset($this->delivery_charge)) $data['delivery_charge']                  = $this->delivery_charge;
        if(isset($this->bank_transaction_charge)) $data['bank_transaction_charge']  = $this->bank_transaction_charge;
        if(isset($this->delivery_name)) $data['delivery_name']                      = $this->delivery_name;
        if(isset($this->delivery_mobile)) $data['delivery_mobile']                  = $this->delivery_mobile;
        if(isset($this->delivery_address)) $data['delivery_address']                = $this->delivery_address;
        if(isset($this->note)) $data['note']                                        = $this->note;
        if(isset($this->voucher_id)) $data['voucher_id']                            = $this->voucher_id;
        return $data + $this->modificationFields(false, true);
    }

    private function setExistingOrderAndSkus() : array
    {
        $previous_order = clone $this->order;
        $existing_order_skus = clone $this->orderSkusRepositoryInterface->where('order_id', $previous_order->id)->latest()->get();
        return [$previous_order, $existing_order_skus];
    }

    private function createLog($previous_order, $existing_order_skus)
    {
        $this->setPreviousOrder($previous_order, $existing_order_skus);
        $this->setNewOrder();
        $this->orderLogCreator->create();
    }

    private function getTypeOfChangeLog() : string
    {
        return $this->orderLogType;
    }

    private function setPreviousOrder($order, $existing_order_skus)
    {
        $this->orderLogCreator->setExistingOrderData($order)
            ->setExistingOrderSkus($existing_order_skus)
            ->setOrderId($this->order_id);
    }

    private function setNewOrder()
    {
        $new_order_skus = $this->orderSkusRepositoryInterface->where('order_id', $this->order_id)->latest()->get();
        $this->orderLogCreator
            ->setChangedOrderData($this->orderRepositoryInterface->find($this->order->id))
            ->setChangedOrderSkus($new_order_skus)
            ->setType($this->getTypeOfChangeLog());
    }
}
