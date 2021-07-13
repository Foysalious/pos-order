<?php namespace App\Repositories;

use App\Interfaces\DiscountRepositoryInterface;
use App\Models\OrderDiscount;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInterface
{
    public function __construct(OrderDiscount $model)
    {
        parent::__construct($model);
    }

}
