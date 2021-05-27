<?php namespace App\Repositories;

use App\Interfaces\PaymentLinkRepositoryInterface;
use App\Models\Payment;
use App\Services\PaymentLink\PaymentLinkClient;
use App\Services\PaymentLink\Target;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use stdClass;

class PaymentLinkRepository extends BaseRepository implements PaymentLinkRepositoryInterface
{
    private PaymentLinkClient $paymentLinkClient;


    /**
     * PaymentLinkRepository constructor.
     * @param PaymentLinkClient $client
     * @param Model $model
     */
    public function __construct(PaymentLinkClient $client, Model $model)
    {
        parent::__construct($model);
        $this->paymentLinkClient = $client;


    }

    /**
     * @param $targets Target[]
     * @return PaymentLinkTransformer[][]
     */
    public function getPaymentLinksByPosOrders(array $targets)
    {
        $links = $this->paymentLinkClient->getPaymentLinksByPosOrders($targets);
        return $this->formatPaymentLinkTransformers($links);
    }

    public function getPaymentLinksByPosOrder($target)
    {
        return $this->getPaymentLinksByPosOrders([$target]);
    }

    public function getActivePaymentLinksByPosOrders(array $targets)
    {
        $links = $this->paymentLinkClient->getActivePaymentLinksByPosOrders($targets);
        return $this->formatPaymentLinkTransformers($links);
    }

    public function getActivePaymentLinkByPosOrder($target)
    {
        $links        = $this->paymentLinkClient->getActivePaymentLinkByPosOrder($target);
        $payment_link = $this->formatPaymentLinkTransformers($links);
        $key          = $target->toString();
        if (array_key_exists($key, $payment_link)) {
            return $payment_link[$key][0];
        }
        return false;
    }
}
