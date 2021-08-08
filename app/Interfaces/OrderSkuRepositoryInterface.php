<?php namespace App\Interfaces;


use phpDocumentor\Reflection\Types\Integer;

interface OrderSkuRepositoryInterface extends BaseRepositoryInterface
{
    public function getNotRatedOrderSkuListOfCustomer($customerId,int $offset, int $limit, string $order);
}
