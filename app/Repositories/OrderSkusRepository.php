<?php


namespace App\Repositories;


use App\Interfaces\OrderSkusRepositoryInterface;
use App\Models\OrderSku;

class OrderSkusRepository extends BaseRepository implements OrderSkusRepositoryInterface
{
    public function __construct(OrderSku $model)
    {
        parent::__construct($model);
    }
}
