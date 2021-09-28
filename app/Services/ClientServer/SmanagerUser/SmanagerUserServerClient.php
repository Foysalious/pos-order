<?php namespace App\Services\ClientServer\SmanagerUser;


use App\Services\ClientServer\BaseClientServer;

class SmanagerUserServerClient extends BaseClientServer
{
    /**
     * @return SmanagerUserServerClient
     */
    public function setBaseUrl()
    {
        $this->baseUrl = rtrim(config('smanager_user.api_url'), '/');
        return $this;
    }
}
