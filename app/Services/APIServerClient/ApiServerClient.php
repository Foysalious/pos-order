<?php namespace App\Services\APIServerClient;


use App\Services\ClientServer\BaseClientServer;

class ApiServerClient extends BaseClientServer
{
    public function setBaseUrl()
    {
        $this->baseUrl = rtrim(config('api.api_url'), '/');
        return $this;
    }
}
