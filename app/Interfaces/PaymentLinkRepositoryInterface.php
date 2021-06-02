<?php namespace App\Interfaces;

use App\Services\PaymentLink\Target;


interface PaymentLinkRepositoryInterface
{
    /**
     * @param $target Target
     */
    public function getActivePaymentLinkByPosOrder(Target $target);
}
