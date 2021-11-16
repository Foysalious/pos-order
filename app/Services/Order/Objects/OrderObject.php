<?php namespace App\Services\Order\Objects;


class OrderObject
{
    protected $id;
    protected $created_at;
    protected $partner_wise_order_id;
    protected $status;
    protected $sales_channel_id;
    protected $delivery_name;
    protected $delivery_mobile;
    protected $delivery_address;
    protected $note;

    /**
     * @param mixed $id
     * @return OrderObject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return OrderObject
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @param mixed $partner_wise_order_id
     * @return OrderObject
     */
    public function setPartnerWiseOrderId($partner_wise_order_id)
    {
        $this->partner_wise_order_id = $partner_wise_order_id;
        return $this;
    }

    /**
     * @param mixed $status
     * @return OrderObject
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param mixed $sales_channel_id
     * @return OrderObject
     */
    public function setSalesChannelId($sales_channel_id)
    {
        $this->sales_channel_id = $sales_channel_id;
        return $this;
    }

    /**
     * @param mixed $delivery_name
     * @return OrderObject
     */
    public function setDeliveryName($delivery_name)
    {
        $this->delivery_name = $delivery_name;
        return $this;
    }

    /**
     * @param mixed $delivery_mobile
     * @return OrderObject
     */
    public function setDeliveryMobile($delivery_mobile)
    {
        $this->delivery_mobile = $delivery_mobile;
        return $this;
    }

    /**
     * @param mixed $delivery_address
     * @return OrderObject
     */
    public function setDeliveryAddress($delivery_address)
    {
        $this->delivery_address = $delivery_address;
        return $this;
    }

    /**
     * @param mixed $note
     * @return OrderObject
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }

}
