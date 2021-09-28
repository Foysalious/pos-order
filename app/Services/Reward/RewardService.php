<?php namespace App\Services\Reward;

use App\Services\APIServerClient\ApiServerClient;

class RewardService
{
    protected array $data;


    public function __construct(
        protected ApiServerClient $apiServerClient){}

    /**
     * @param array $data
     * @return RewardService
     */
    public function setData(array $data): RewardService
    {
        $this->data = $data;
        return $this;
    }

    public function store()
    {
        $this->apiServerClient->setBaseUrl()->post( 'pos/v1/reward/action', $this->data);
    }

}
