<?php namespace App\Interfaces;

use App\Services\PaymentLink\Target;


interface PaymentLinkRepositoryInterface
{
    /**
     * @param $target Target
     */
    public function getActivePaymentLinkByPosOrder(Target $target);

    /**
     * @param array $attributes
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(array $attributes);
}
