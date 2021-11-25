<?php namespace App\Services\OrderLog\Objects;


use App\Models\Customer;
use JsonSerializable;

class CustomerObject implements JsonSerializable
{
    private ?string $id;
    private ?string $name;
    private ?int $partner_id;
    private ?int $is_supplier;
    private ?string $email;
    private ?string $mobile;
    private ?string $pro_pic;
    private ?string $deleted_at;
    private ?string $created_at;
    private ?string $updated_at;
    private ?string $created_by_name;
    private ?string $updated_by_name;
    private ?Customer $customer;


    /**
     * @param Customer|null $customer
     * @return $this
     */
    public function setCustomer(?Customer $customer): CustomerObject
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param mixed $id
     * @return CustomerObject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $name
     * @return CustomerObject
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $partner_id
     * @return CustomerObject
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param mixed $is_supplier
     * @return CustomerObject
     */
    public function setIsSupplier($is_supplier)
    {
        $this->is_supplier = $is_supplier;
        return $this;
    }

    /**
     * @param mixed $email
     * @return CustomerObject
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param mixed $mobile
     * @return CustomerObject
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @param mixed $pro_pic
     * @return CustomerObject
     */
    public function setProPic($pro_pic)
    {
        $this->pro_pic = $pro_pic;
        return $this;
    }

    /**
     * @param mixed $deleted_at
     * @return CustomerObject
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return CustomerObject
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @param mixed $updated_at
     * @return CustomerObject
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @param mixed $created_by_name
     * @return CustomerObject
     */
    public function setCreatedByName($created_by_name)
    {
        $this->created_by_name = $created_by_name;
        return $this;
    }

    /**
     * @param mixed $updated_by_name
     * @return CustomerObject
     */
    public function setUpdatedByName($updated_by_name)
    {
        $this->updated_by_name = $updated_by_name;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
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
