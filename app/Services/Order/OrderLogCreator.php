<?php namespace App\Services\Order;


use App\Http\Resources\OrderWithProductResource;
use App\Interfaces\OrderLogRepositoryInterface;
use App\Traits\ModificationFields;
use Carbon\Carbon;

class OrderLogCreator
{
    use ModificationFields;
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
        if(isset($this->existingOrder)) $data['old_value']      = $this->existingOrder;
        if(isset($this->changedOrderData)) $data['new_value']   = $this->changedOrderData;
        return $data + $this->modificationFields(true, false);
    }

    private function makeLogDataToJSON($order)
    {
        $resource = new OrderWithProductResource($order);
        return json_encode($resource);
    }
}
