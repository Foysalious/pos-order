<?php namespace App\Repositories;

use App\Interfaces\PaymentLinkRepositoryInterface;
use App\Services\PaymentLink\PaymentLinkClient;
use App\Services\PaymentLink\PaymentLinkTransformer;
use App\Services\PaymentLink\Target;

class PaymentLinkRepository implements PaymentLinkRepositoryInterface
{
    private PaymentLinkClient $paymentLinkClient;
    private PaymentLinkTransformer $paymentLinkTransformer;

    /**
     * PaymentLinkRepository constructor.
     * @param PaymentLinkTransformer $paymentLinkTransformer
     * @param PaymentLinkClient $client
     */
    public function __construct(PaymentLinkTransformer $paymentLinkTransformer, PaymentLinkClient $client)
    {
        $this->paymentLinkClient = $client;
        $this->paymentLinkTransformer = $paymentLinkTransformer;
    }

    /**
     * @param Target $target
     * @return false|mixed
     */
    public function getActivePaymentLinkByPosOrder(Target $target): mixed
    {
        $links        = $this->paymentLinkClient->getActivePaymentLinkByPosOrder($target);
        $payment_link = $this->formatPaymentLinkTransformers($links);
        $key          = $target->toString();
        if (array_key_exists($key, $payment_link)) {
            return $payment_link[$key][0];
        }
        return false;
    }

    /**
     * @param $links
     * @return array
     */
    private function formatPaymentLinkTransformers($links): array
    {
        $result = [];
        foreach ($links as $link) {
            $link = $this->paymentLinkTransformer->setResponse(json_decode(json_encode($link)));
            array_push_on_array($result, $link->getUnresolvedTarget()->toString(), $link);
        }
        return $result;
    }

    public function create(array $attributes)
    {
        return $this->paymentLinkClient->storePaymentLink($attributes);
    }

    public function statusUpdate($link, $status)
    {
        return $this->paymentLinkClient->paymentLinkStatusChange($link, $status);
    }
}
