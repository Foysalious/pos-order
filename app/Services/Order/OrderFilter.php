<?php namespace App\Services\Order;


class OrderFilter
{
    protected $type;

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
