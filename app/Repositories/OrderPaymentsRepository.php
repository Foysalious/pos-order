<?php namespace App\Repositories;


use App\Interfaces\OrderSkusRepositoryInterface;
use App\Models\OrderPayment;

class OrderPaymentsRepository extends BaseRepository implements OrderSkusRepositoryInterface
{
    public function __construct(OrderPayment $model)
    {
        parent::__construct($model);
    }
}
