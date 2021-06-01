<?php namespace App\Services\Order;


class OrderFilter
{
    protected $type, $order_status, $payment_status;

    /**
     * @return mixed
     */
    public function getPaymentStatus()
    {
        return $this->payment_status;
    }

    /**
     * @param mixed $payment_status
     * @return OrderFilter
     */
    public function setPaymentStatus($payment_status)
    {
        $this->payment_status = $payment_status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderStatus()
    {
        return $this->order_status;
    }

    /**
     * @param mixed $order_status
     * @return OrderFilter
     */
    public function setOrderStatus($order_status)
    {
        $this->order_status = $order_status;
        return $this;
    }

    /**
     * @param mixed $type
     * @return OrderFilter
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
