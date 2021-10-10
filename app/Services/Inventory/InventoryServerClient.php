<?php namespace App\Services\Inventory;


use App\Services\ClientServer\BaseClientServer;

class InventoryServerClient extends BaseClientServer
{
    public function getBaseUrl()
    {
        return rtrim(config('inventory.api_url'), '/');
    }
}
