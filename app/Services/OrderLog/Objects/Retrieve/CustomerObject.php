<?php namespace App\Services\OrderLog\Objects\Retrieve;


class CustomerObject
{
    private $id;
    private $name;
    private $partner_id;
    private $is_supplier;
    private $email;
    private $mobile;
    private $pro_pic;
    private $deleted_at;
    private $created_at;
    private $updated_at;
    private $created_by_name;
    private $updated_by_name;

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


}
