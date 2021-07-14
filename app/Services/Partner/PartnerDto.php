<?php namespace App\Services\Partner;

use Spatie\DataTransferObject\DataTransferObject;

class PartnerDto extends DataTransferObject
{
    public ?string $name;
    public ?string $sub_domain;
    public ?bool $sms_invoice;
    public ?bool $auto_printing;
    public ?string $printer_name;
    public ?string $printer_model;
}
