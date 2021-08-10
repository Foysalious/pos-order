<?php namespace App\Repositories;


use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\OrderSku;

class OrderSkuRepository extends BaseRepository implements OrderSkuRepositoryInterface
{
    public function __construct(OrderSku $model)
    {
        parent::__construct($model);
    }

    public function getNotRatedOrderSkuListOfCustomer($partner_id,$customerId,int $offset, int $limit, string $order)
    {
        return  $this->model->whereHas('order',function($q)use($customerId){
            $q->where('customer_id',$customerId);
        })->doesntHave('review')->offset($offset)->limit($limit)->orderBy('created_at',$order)->get();
    }


}
