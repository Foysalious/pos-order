<?php namespace App\Services\OnlinePayment;


use Spatie\DataTransferObject\DataTransferObject;

class CreatorDto extends DataTransferObject
{
    public float $amount;
    public ?int $emi_month;
    public ?int $interest_paid_by;
    public ?int $transaction_charge;
    public int|string $customer_id;
}
