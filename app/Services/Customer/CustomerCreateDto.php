<?php namespace App\Services\Customer;


use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class CustomerCreateDto extends DataTransferObject
{
    public string $id;
    public string $name;
    public string $email;
    public string $mobile;
    public string $partner_id;
    public string $pro_pic;
}
