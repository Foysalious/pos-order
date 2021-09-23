<?php namespace App\Interfaces;


use phpDocumentor\Reflection\Types\Integer;

interface OrderSkuRepositoryInterface extends BaseRepositoryInterface
{
    public function getNotRatedOrderSkuListOfCustomer($partner_id,$customerId,int $offset, int $limit, string $order);
    public function getNotRatedOrderSkuListOfCustomerCount($partner_id,$customerId, string $order);
}
