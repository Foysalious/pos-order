<?php namespace App\Repositories;


use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Models\OrderPayment;

class OrderPaymentRepository extends BaseRepository implements OrderPaymentRepositoryInterface
{
    public function __construct(OrderPayment $model)
    {
        parent::__construct($model);
    }

}
