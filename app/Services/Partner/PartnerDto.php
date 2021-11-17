<?php namespace App\Services\Partner;

use Spatie\DataTransferObject\DataTransferObject;

class PartnerDto extends DataTransferObject
{
    public int $id;
    public ?string $name;
    public ?string $sub_domain;
    public ?bool $sms_invoice;
    public ?bool $auto_printing;
    public ?string $printer_name;
    public ?string $printer_model;
    public ?string $qr_code_image;
    public ?string $qr_code_account_type;
}
