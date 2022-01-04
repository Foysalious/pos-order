<?php namespace App\Repositories;


use App\Interfaces\OrderPaymentsRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\OrderPayment;

class OrderPaymentsRepository extends BaseRepository implements OrderPaymentsRepositoryInterface
{
    public function __construct(OrderPayment $model)
    {
        parent::__construct($model);
    }
}
