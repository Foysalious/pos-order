<?php


namespace App\Interfaces;


interface OrderSkusRepositoryInterface extends BaseRepositoryInterface
{
    public function updateOrderSkus($partner_id, $skus, $order_id);
}
