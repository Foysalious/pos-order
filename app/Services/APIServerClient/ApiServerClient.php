<?php namespace App\Services\APIServerClient;


use App\Services\ClientServer\BaseClientServer;

class ApiServerClient extends BaseClientServer
{
    public function getBaseUrl()
    {
        return rtrim(config('api.api_url'), '/');
    }
}
