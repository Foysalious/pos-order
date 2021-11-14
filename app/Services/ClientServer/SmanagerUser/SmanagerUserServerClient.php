<?php namespace App\Services\ClientServer\SmanagerUser;


use App\Services\ClientServer\BaseClientServer;

class SmanagerUserServerClient extends BaseClientServer
{

    public function getBaseUrl()
    {
       return rtrim(config('smanager_user.api_url'), '/');
    }
}
