<?php namespace App\Interfaces;


interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrderListWithPagination($offset, $limit, $partner_id, $orderSearch, $orderFilter);
    public function getCustomerOrderList($customer_id,$offset, $limit);
}
