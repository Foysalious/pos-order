<?php namespace App\Services\Order;


use App\Interfaces\OrderLogRepositoryInterface;

class OrderLogCreator
{
    protected $existingOrder, $changedOrderData;
    protected $orderLogRepositoryInterface;

    public function __construct(OrderLogRepositoryInterface $orderLogRepositoryInterface)
    {
        $this->orderLogRepositoryInterface = $orderLogRepositoryInterface;
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
        return $this->orderLogRepositoryInterface->create($this->makeLogData());
    }

    private function makeLogData()
    {
        $data = [];
        dd($this->existingOrder);
        return $data;
    }
}
