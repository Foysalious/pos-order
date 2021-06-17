<?php namespace App\Interfaces;


interface OrderSkuRepositoryInterface extends BaseRepositoryInterface
{
public function getNotRatedOrderSkuListOfCustomer($customerId);
}
