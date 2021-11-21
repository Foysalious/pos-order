<?php namespace App\Services\OrderLog\Objects\Store;


use App\Models\OrderDiscount;
use JsonSerializable;

class DiscountObject implements JsonSerializable
{
    private OrderDiscount $discount;

    /**
     * @param mixed $discount
     * @return DiscountObject
     */
    public function setDiscount(OrderDiscount $discount): DiscountObject
    {
        $this->discount = $discount;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->discount->id,
            'order_id' => $this->discount->order_id,
            'type' => $this->discount->type,
            'amount' => $this->discount->amount,
            'original_amount' => $this->discount->original_amount,
            'is_percentage' => $this->discount->is_percentage,
            'cap' => $this->discount->cap,
            'discount_details' => $this->discount->discount_details,
            'discount_id' => $this->discount->discount_id,
            'type_id' => $this->discount->type_id,
            'created_by_name' => $this->discount->created_by_name,
            'updated_by_name' => $this->discount->updated_by_name,
            'created_at' => $this->discount->created_at,
            'updated_at' => $this->discount->updated_at,
            'deleted_at' => $this->discount->deleted_at,
        ];
    }
}
