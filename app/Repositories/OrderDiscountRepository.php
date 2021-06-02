<?php namespace App\Repositories;


use App\Interfaces\OrderDiscountRepositoryInterface;
use App\Models\OrderDiscount;

class OrderDiscountRepository extends BaseRepository implements OrderDiscountRepositoryInterface
{
    public function __construct(OrderDiscount $model)
    {
        parent::__construct($model);
    }
}
