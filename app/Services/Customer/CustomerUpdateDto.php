<?php namespace App\Services\Customer;


use Spatie\DataTransferObject\DataTransferObject;

class CustomerUpdateDto extends DataTransferObject
{
    public ?string $name;
    public ?string $email;
    public ?string $mobile;
    public ?string $pro_pic;
    public ?string $is_supplier;
}
