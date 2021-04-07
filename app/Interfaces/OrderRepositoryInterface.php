<?php


namespace App\Interfaces;


interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrderListWithOffsetLimitAndPartner($offset, $limit, $partner_id);
}
