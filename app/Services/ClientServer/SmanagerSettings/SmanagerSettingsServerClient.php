<?php namespace App\Services\ClientServer\SmanagerSettings;

use App\Services\ClientServer\BaseClientServer;

class SmanagerSettingsServerClient extends BaseClientServer
{
    public function getBaseUrl()
    {
        return rtrim(config('sheba.smanager_settings_api_url'), '/');
    }
}
