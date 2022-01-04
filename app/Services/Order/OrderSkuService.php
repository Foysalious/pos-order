<?php


namespace App\Services\Order;


use App\Interfaces\OrderSkuRepositoryInterface;
use App\Services\BaseService;

class OrderSkuService extends BaseService
{
    public $OrderSkuRepositoryInterface;

    public function __construct(OrderSkuRepositoryInterface $OrderSkuRepositoryInterface)
    {
        $this->OrderSkuRepositoryInterface = $OrderSkuRepositoryInterface;
    }
}
