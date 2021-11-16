<?php namespace App\Services\Order\Objects;


class OrderCustomerObject
{
    protected ?string $name;
    protected ?string $mobile;
    protected ?string $pro_pic;

    /**
     * @param string|null $name
     * @return OrderCustomerObject
     */
    public function setName(?string $name): OrderCustomerObject
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string|null $mobile
     * @return $this
     */
    public function setMobile(?string $mobile): OrderCustomerObject
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @param string|null $pro_pic
     * @return OrderCustomerObject
     */
    public function setProPic(?string $pro_pic): OrderCustomerObject
    {
        $this->pro_pic = $pro_pic;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }



}
