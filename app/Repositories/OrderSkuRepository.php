<?php namespace App\Repositories;


use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\OrderSku;

class OrderSkuRepository extends BaseRepository implements OrderSkuRepositoryInterface
{
    public function __construct(OrderSku $model)
    {
        parent::__construct($model);
    }


}
