<?php namespace App\Interfaces;

use App\Services\PaymentLink\Target;


interface PaymentLinkRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param $targets Target[]
     */
    public function getPaymentLinksByPosOrders(array $targets);

    public function getPaymentLinksByPosOrder($target);

    public function getActivePaymentLinksByPosOrders(array $targets);

    public function getActivePaymentLinkByPosOrder($target);
}
