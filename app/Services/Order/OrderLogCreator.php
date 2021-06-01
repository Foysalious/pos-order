<?php namespace App\Services\Order;


use App\Interfaces\OrderLogRepositoryInterface;
use Carbon\Carbon;

class OrderLogCreator
{
    protected $existingOrder, $changedOrderData, $orderId, $type, $existingOrderSkus, $newOrderSkus, $orderStatus;
    protected $orderLogRepository;

    public function __construct(OrderLogRepositoryInterface $orderLogRepository)
    {
        $this->orderLogRepository = $orderLogRepository;
    }

    /**
     * @param mixed $newOrderSkus
     * @return OrderLogCreator
     */
    public function setChangedOrderSkus($newOrderSkus)
    {
        $this->newOrderSkus = $newOrderSkus;
        return $this;
    }

    /**
     * @param mixed $orderSkus
     * @return OrderLogCreator
     */
    public function setExistingOrderSkus($orderSkus)
    {
        $this->existingOrderSkus = $orderSkus;
        return $this;
    }

    /**
     * @param mixed $type
     * @return OrderLogCreator
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $orderId
     * @return OrderLogCreator
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @param mixed $changedOrderData
     * @return orderLogCreator
     */
    public function setChangedOrderData($changedOrderData)
    {
        $this->changedOrderData = $changedOrderData;
        return $this;
    }

    /**
     * @param mixed $order
     * @return orderLogCreator
     */
    public function setExistingOrderData($order)
    {
        $this->existingOrder = $order;
        return $this;
    }

    /**
     * @param mixed $orderStatus
     * @return OrderLogCreator
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;
        return $this;
    }

    public function create()
    {
        return $this->orderLogRepository->insert($this->makeOrderLogData());
    }

    private function makeOrderLogData()
    {
        $data = [];
        if(isset($this->orderId)) $data['order_id']             = $this->orderId;
        if(isset($this->type)) $data['type']                    = $this->type;
        if(isset($this->existingOrder)) $data['old_value']      = $this->makeLogDataToJSON($this->existingOrder, $this->existingOrderSkus) ?? '';
        if(isset($this->changedOrderData)) $data['new_value']   = $this->makeLogDataToJSON($this->changedOrderData, $this->newOrderSkus) ?? '';
        $data['created_by_name']                                = 'anonymous';
        $data['created_at']                                     = Carbon::now();
        return $data;
    }

    private function makeLogDataToJSON($order, $skus)
    {
        $data = [];
        $data['order_id']                   = $order->id;
        $data['partner_wise_order_id']      = $order->partner_wise_order_id;
        $data['partner_id']                 = $order->partner_id;
        $data['customer_id']                = $order->customer_id;
        $data['status']                     = $order->status;
        $data['sales_channel_id']           = $order->sales_channel_id;
        $data['emi_month']                  = isset($order->emi_month) ? $order->emi_month : "";
        $data['interest']                   = isset($order->interest) ? $order->interest : "";
        $data['delivery_charge']            = $order->interest->delivery_charge ?? 0.00;
        $data['bank_transaction_charge']    = isset($order->bank_transaction_charge) ? $order->bank_transaction_charge : "";
        $data['delivery_name']              = isset($order->delivery_name) ? $order->delivery_name : "";
        $data['delivery_name']              = isset($order->delivery_mobile) ? $order->delivery_mobile : "";
        $data['delivery_name']              = isset($order->delivery_address) ? $order->delivery_address : "";
        $data['note']                       = isset($order->note) ? $order->note : "";
        $data['voucher_id']                 = isset($order->voucher_id) ? $order->voucher_id : "";
        $data['products']                   = isset($skus) ? $skus : [];
        return json_encode($data);
    }
}
