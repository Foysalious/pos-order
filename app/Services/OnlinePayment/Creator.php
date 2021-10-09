<?php namespace App\Services\OnlinePayment;


use App\Services\APIServerClient\ApiServerClient;
use App\Services\Order\PriceCalculation;
use App\Services\Order\Updater;

class Creator
{
    private CreatorDto $creatorDto;

    public function __construct(private ApiServerClient $apiServerClient, private PriceCalculation $priceCalculation, private Updater $updater)
    {
    }

    /**
     * @param CreatorDto $creatorDto
     * @return Creator
     */
    public function setCreatorDto(CreatorDto $creatorDto): Creator
    {
        $this->creatorDto = $creatorDto;
        return $this;
    }

    public function initiate()
    {
        $payment_link = $this->apiServerClient->post('pos/v1/partners/' . $this->creatorDto->order->partner_id . '/orders/' .
            $this->creatorDto->order->id . '/payment/create', [
            'amount' => $this->priceCalculation->setOrder($this->creatorDto->order)->getDiscountedPrice(),
            'purpose' => $this->creatorDto->purpose,
            'pos_order_id' => $this->creatorDto->order->id,
            'customer_id' => $this->creatorDto->order->customer_id
        ]);
        if ($payment_link['code'] == 200) $this->updater->setOrder($this->creatorDto->order);
        return $payment_link;
    }
}
