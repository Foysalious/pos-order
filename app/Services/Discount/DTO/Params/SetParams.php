<?php namespace App\Services\Discount\DTO\Params;


use App\Models\Order;

abstract class SetParams
{
    protected $type;
    /** @var Order $order */
    protected $order;

    public abstract function getData();

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }
}
