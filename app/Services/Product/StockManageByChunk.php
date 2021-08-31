<?php


namespace App\Services\Product;


class StockManageByChunk extends StockManager
{

    public function manageStock(array $data)
    {
        $this->client->put($data);
    }
}
