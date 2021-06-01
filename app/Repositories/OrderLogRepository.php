<?php namespace App\Repositories;


use App\Interfaces\OrderLogRepositoryInterface;
use App\Models\OrderLog;

class OrderLogRepository extends BaseRepository implements OrderLogRepositoryInterface
{
    public function __construct(OrderLog $model)
    {
        parent::__construct($model);
    }
}
