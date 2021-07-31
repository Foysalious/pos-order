<?php namespace App\Services\Order\Objects;


class OrderCustomerObject
{
    protected $customerDetails;
    protected ?string $name;
    protected ?string $phone;
    protected ?string $pro_pic;

    /**
     * @param mixed $customerDetails
     * @return OrderCustomerObject
     */
    public function setCustomerDetails($customerDetails)
    {
        $this->customerDetails = $customerDetails;
        return $this;
    }

    public function build()
    {
        $this->name = $this->customerDetails->name;
        $this->phone = $this->customerDetails->phone;
        $this->pro_pic = $this->customerDetails->pro_pic;
        return $this;
    }


}
