<?php namespace App\Services\Order;


use App\Interfaces\OrderLogRepositoryInterface;
use Carbon\Carbon;

class OrderLogCreator
{
    protected $existingOrder, $changedOrderData, $orderId, $type;
    protected $orderLogRepository;

    public function __construct(OrderLogRepositoryInterface $orderLogRepository)
    {
        $this->orderLogRepository = $orderLogRepository;
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

    public function create()
    {
        return $this->orderLogRepository->insert($this->makeLogData());
    }

    private function makeLogData()
    {
        $data = [];
        if(isset($this->orderId)) $data['order_id'] = $this->orderId;
        if(isset($this->type)) $data['type'] = $this->type;
        if(isset($this->existingOrder)) $data['old_value'] = $this->makeLogDataToJSON() ?? '';
        if(isset($this->changedOrderData)) $data['new_value'] = $this->makeLogDataToJSON() ?? '';
        return $data;
    }

    private function makeLogDataToJSON()
    {
        $data = [];
        $data['order_id'] = $this->existingOrder->id ?? $this->changedOrderData->id;
        $data['partner_wise_order_id'] = $this->existingOrder->partner_wise_order_id ?? $this->changedOrderData->partner_wise_order_id;
        $data['partner_id'] = $this->existingOrder->partner_id ?? $this->changedOrderData->partner_id;
        $data['customer_id'] = $this->existingOrder->customer_id ?? $this->changedOrderData->customer_id;
        $data['status'] = $this->existingOrder->status ?? $this->changedOrderData->status;
        $data['sales_channel_id'] = $this->existingOrder->sales_channel_id ?? $this->changedOrderData->sales_channel_id;
        $data['emi_month'] = (isset($this->existingOrder->emi_month) ?: "") ?? (isset($this->changedOrderData->emi_month) ?: "");
        $data['interest'] = (isset($this->existingOrder->interest) ?: "") ?? (isset($this->changedOrderData->interest) ?: "");
        $data['delivery_charge'] = $this->existingOrder->delivery_charge ?? $this->changedOrderData->delivery_charge;
        $data['bank_transaction_charge'] = (isset($this->existingOrder->bank_transaction_charge) ?: "") ?? (isset($this->changedOrderData->bank_transaction_charge) ?: "");
        $data['delivery_name'] = $this->existingOrder->delivery_name ?? $this->changedOrderData->delivery_name;
        $data['delivery_mobile'] = $this->existingOrder->delivery_mobile ?? $this->changedOrderData->delivery_mobile;
        $data['delivery_address'] = $this->existingOrder->delivery_address ?? $this->changedOrderData->delivery_address;
        $data['note'] = (isset($this->existingOrder->note) ?: "") ?? (isset($this->changedOrderData->note) ?: "");
        $data['voucher_id'] = (isset($this->existingOrder->voucher_id) ?: "") ?? (isset($this->changedOrderData->voucher_id) ?: "");
        return json_encode($data);
    }
}
