<?php namespace App\Repositories;


use App\Interfaces\PaymentRepositoryInterface;
use App\Models\OrderPayment;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    public function __construct(OrderPayment $model)
    {
        parent::__construct($model);
    }
}
