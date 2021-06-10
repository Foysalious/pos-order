<?php namespace App\Services\Customer;


use Spatie\DataTransferObject\DataTransferObject;

class CustomerUpdateDto extends DataTransferObject
{
    public string $id;
    public string $name;
    public string $email;
    public string $phone;
    public string $pro_pic;
}
