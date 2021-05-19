<?php

namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Repositories\BaseRepository;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getOrderListWithOffsetLimitAndPartner($offset, $limit, $partner_id, $orderSearch)
    {
        $order_id = $orderSearch->getOrderId();
        $customer_name = $orderSearch->getCustomerName();
        $query_string = $orderSearch->getQueryString();
        $sales_channel_id = $orderSearch->getSalesChannel();

        $queryOrderList = $this->model->where('partner_id', $partner_id)->whereHas('customer', function ($query) use ($customer_name) {
            $query->where('name', 'like', '%'. $customer_name .'%');
        })->when($order_id, function ($query) use ($order_id) {
            return $query->where('partner_wise_order_id', $order_id);
        })->when($sales_channel_id, function ($query) use ($sales_channel_id) {
            return $query->where('sales_channel_id', $sales_channel_id);
        })->when($query_string, function ($query) use ($query_string) {
            $query->where(function ($whereQuery) use ($query_string) {
                $whereQuery->where('delivery_name', 'LIKE', '%'.$query_string.'%')
                    ->orWhere('delivery_mobile', 'LIKE', '%'.$query_string.'%')
                    ->orWhere('delivery_address', 'LIKE', '%'.$query_string.'%');
            });
        });
        return $queryOrderList->offset($offset)->limit($limit)->latest()->get();
    }
}
