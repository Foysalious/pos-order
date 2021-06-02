<?php


namespace App\Services\Order;


use App\Interfaces\OrderSkusRepositoryInterface;
use App\Services\BaseService;

class OrderSkuService extends BaseService
{
    public $orderSkusRepositoryInterface;

    public function __construct(OrderSkusRepositoryInterface $orderSkusRepositoryInterface)
    {
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
    }
}
