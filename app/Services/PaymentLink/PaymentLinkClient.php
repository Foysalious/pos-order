<?php namespace App\Services\PaymentLink;

use App\Services\PaymentLink\Constants\TargetType;
use GuzzleHttp\Client;
use Exception;

class PaymentLinkClient
{
    /** @var string */
    private string $baseUrl;
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->baseUrl = config('pos.payment_link_url') . '/api/v1/payment-links';
        $this->client = $client;
    }


    public function getActivePaymentLinksByPosOrders(array $targets)
    {
        try {
            $targets = array_filter(array_map(function (Target $target) {
                if ($target->getType() != TargetType::POS_ORDER) return null;
                return $target->getId();
            }, $targets));
            if (empty($targets)) return [];
            $uri = $this->baseUrl . '?posOrders=' . implode(",", $targets) . '&isActive=' . 1;
            $response = json_decode($this->client->get($uri)->getBody()->getContents(), true);
            if ($response['code'] != 200) return [];
            return $response['links'];
        } catch (Exception $e) {
            return [];
        }

    }
}
