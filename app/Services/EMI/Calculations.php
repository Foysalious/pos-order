<?php namespace App\Services\EMI;


use App\Services\APIServerClient\ApiServerClient;
use App\Services\ClientServer\Exceptions\BaseClientServerError;

class Calculations
{
    protected int $emi_month;
    protected float $amount;

    public function __construct(protected ApiServerClient $client)
    {
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount(float $amount): Calculations
    {
        $this->amount =  $amount;
        return $this;
    }

    /**
     * @param int $emi_month
     * @return $this
     */
    public function setEmiMonth(int $emi_month): Calculations
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @throws BaseClientServerError
     */
    public function getEmiCharges()
    {
        $uri = $this->getUriWithQueryParams();
        return $this->client->get($uri);
    }

    private function getUriWithQueryParams()
    {
        return "/pos/v1/emi-calculate?" . "amount=$this->amount&" . "emi_month=$this->emi_month";
    }

}
