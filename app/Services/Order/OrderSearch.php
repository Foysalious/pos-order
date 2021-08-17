<?php namespace App\Services\Order;

class OrderSearch
{
    protected ?int $order_id;
    protected ?string $customer_name;
    protected ?string $query_string;
    protected $sales_channel_id;

    /**
     * @return mixed
     */
    public function getSalesChannelId()
    {
        return $this->sales_channel_id;
    }

    /**
     * @param mixed $sales_channel_id
     * @return OrderSearch
     */
    public function setSalesChannelId($sales_channel_id)
    {
        $this->sales_channel_id = $sales_channel_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQueryString()
    {
        return $this->query_string;
    }

    /**
     * @param mixed $query_string
     * @return OrderSearch
     */
    public function setQueryString($query_string)
    {
        $this->query_string = $query_string;
        return $this;
    }

    /**
     * @param mixed $order_id
     * @return OrderSearch
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param mixed $customer_name
     * @return OrderSearch
     */
    public function setCustomerName($customer_name)
    {
        $this->customer_name = $customer_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }
}
