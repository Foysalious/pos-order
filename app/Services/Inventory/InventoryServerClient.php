<?php namespace App\Services\Inventory;


use App\Services\ClientServer\BaseClientServer;

class InventoryServerClient extends BaseClientServer
{
    public function setBaseUrl()
    {
        $this->baseUrl = rtrim(config('inventory.api_url'), '/');
        return $this;
    }
}
