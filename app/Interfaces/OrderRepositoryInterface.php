<?php namespace App\Interfaces;


interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrderListWithPagination($offset, $limit, $partner_id, $orderSearch);
}
