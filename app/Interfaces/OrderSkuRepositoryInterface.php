<?php namespace App\Interfaces;


use phpDocumentor\Reflection\Types\Integer;

interface OrderSkuRepositoryInterface extends BaseRepositoryInterface
{
    public function getNotRatedOrderSkuListOfCustomer(string $customerId,int $offset, int $limit);
}
