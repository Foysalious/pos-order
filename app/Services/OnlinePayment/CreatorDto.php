<?php namespace App\Services\OnlinePayment;


use App\Models\Order;
use Spatie\DataTransferObject\DataTransferObject;

class CreatorDto extends DataTransferObject
{
    public Order $order;
    public ?int $emi_month;
    public string $purpose;
    public ?string $interest_paid_by;
    public ?int $transaction_charge;
}
