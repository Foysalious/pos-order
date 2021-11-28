<?php namespace App\Services\OrderLog\Objects;


use App\Models\OrderDiscount;
use JsonSerializable;

class DiscountObject implements JsonSerializable
{
    private ?int $id;
    private ?int $order_id;
    private ?string $type;
    private ?float $amount;
    private ?string $original_amount;
    private ?int $is_percentage;
    private ?float $cap;
    private ?string $discount_details;
    private ?int $discount_id;
    private ?string $type_id;
    private ?string $created_by_name;
    private ?string $updated_by_name;
    private ?string $created_at;
    private ?string $updated_at;
    private ?string $deleted_at;

    private OrderDiscount $orderDiscount;


    /**
     * @param OrderDiscount $orderDiscount
     * @return $this
     */
    public function setOrderDiscount(OrderDiscount $orderDiscount): DiscountObject
    {
        $this->orderDiscount = $orderDiscount;
        return $this;
    }

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

    public function jsonSerialize()
    {
        return [
            'id' => $this->orderDiscount->id,
            'order_id' => $this->orderDiscount->order_id,
            'type' => $this->orderDiscount->type,
            'amount' => $this->orderDiscount->amount,
            'original_amount' => $this->orderDiscount->original_amount,
            'is_percentage' => $this->orderDiscount->is_percentage,
            'cap' => $this->orderDiscount->cap,
            'discount_details' => $this->orderDiscount->discount_details,
            'discount_id' => $this->orderDiscount->discount_id,
            'type_id' => $this->orderDiscount->type_id,
            'created_by_name' => $this->orderDiscount->created_by_name,
            'updated_by_name' => $this->orderDiscount->updated_by_name,
            'created_at' => $this->orderDiscount->created_at,
            'updated_at' => $this->orderDiscount->updated_at,
            'deleted_at' => $this->orderDiscount->deleted_at,
        ];
    }

}
