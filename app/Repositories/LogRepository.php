<?php namespace App\Repositories;


use App\Interfaces\LogRepositoryInterface;
use App\Models\OrderLog;

class LogRepository extends BaseRepository implements LogRepositoryInterface
{
    public function __construct(OrderLog $model)
    {
        parent::__construct($model);
    }

}
