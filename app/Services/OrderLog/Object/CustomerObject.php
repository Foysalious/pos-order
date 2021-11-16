<?php namespace App\Services\OrderLog\Object;


use App\Models\Customer;
use JsonSerializable;

class CustomerObject implements JsonSerializable
{
    private Customer $customer;
    /**
     * @param Customer $customer
     * @return CustomerObject
     */
    public function setCustomer(Customer $customer): CustomerObject
    {
        $this->customer = $customer;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->customer->id,
            'name' => $this->customer->name,
            'partner_id' => $this->customer->partner_id,
            'is_supplier' => $this->customer->is_supplier,
            'email' => $this->customer->email,
            'mobile' => $this->customer->mobile,
            'pro_pic' => $this->customer->pro_pic,
            'deleted_at' => $this->customer->deleted_at,
            'created_at' => $this->customer->created_at,
            'updated_at' => $this->customer->updated_at,
            'created_by_name' => $this->customer->created_by_name,
            'updated_by_name' => $this->customer->updated_by_name,
        ];
    }
}
