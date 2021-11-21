<?php namespace App\Services\OrderLog\Objects\Retrieve;


class DiscountObject
{
    private $id;
    private $order_id;
    private $type;
    private $amount;
    private $original_amount;
    private $is_percentage;
    private $cap;
    private $discount_details;
    private $discount_id;
    private $type_id;
    private $created_by_name;
    private $updated_by_name;
    private $created_at;
    private $updated_at;
    private $deleted_at;

    /**
     * @param mixed $id
     * @return DiscountObject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $order_id
     * @return DiscountObject
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param mixed $discount
     * @return DiscountObject
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param mixed $type
     * @return DiscountObject
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return DiscountObject
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $original_amount
     * @return DiscountObject
     */
    public function setOriginalAmount($original_amount)
    {
        $this->original_amount = $original_amount;
        return $this;
    }

    /**
     * @param mixed $is_percentage
     * @return DiscountObject
     */
    public function setIsPercentage($is_percentage)
    {
        $this->is_percentage = $is_percentage;
        return $this;
    }

    /**
     * @param mixed $cap
     * @return DiscountObject
     */
    public function setCap($cap)
    {
        $this->cap = $cap;
        return $this;
    }

    /**
     * @param mixed $discount_details
     * @return DiscountObject
     */
    public function setDiscountDetails($discount_details)
    {
        $this->discount_details = $discount_details;
        return $this;
    }

    /**
     * @param mixed $discount_id
     * @return DiscountObject
     */
    public function setDiscountId($discount_id)
    {
        $this->discount_id = $discount_id;
        return $this;
    }

    /**
     * @param mixed $type_id
     * @return DiscountObject
     */
    public function setTypeId($type_id)
    {
        $this->type_id = $type_id;
        return $this;
    }

    /**
     * @param mixed $created_by_name
     * @return DiscountObject
     */
    public function setCreatedByName($created_by_name)
    {
        $this->created_by_name = $created_by_name;
        return $this;
    }

    /**
     * @param mixed $updated_by_name
     * @return DiscountObject
     */
    public function setUpdatedByName($updated_by_name)
    {
        $this->updated_by_name = $updated_by_name;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return DiscountObject
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @param mixed $updated_at
     * @return DiscountObject
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @param mixed $deleted_at
     * @return DiscountObject
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }

}
