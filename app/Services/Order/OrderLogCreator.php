<?php namespace App\Services\Order;


class orderLogCreator
{
    protected $order;

    /**
     * @param mixed $order
     * @return orderLogCreator
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function create()
    {
        dd($this->order->items);
        return 'yes';
    }
}
