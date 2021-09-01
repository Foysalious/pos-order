<?php namespace App\Services\Product;


class StockManageByChunk extends StockManager
{
    public function updateStock()
    {
        $this->client->setBaseUrl()->put($this->uri,[ 'data' => $this->data ]);
    }

    public function increaseAndInsertInChunk($quantity)
    {
        $this->data [] = [
                'id' => $this->sku['id'],
                'product_id' => $this->sku['product_id'],
                'operation' => self::STOCK_INCREMENT,
                'quantity' => (float) $quantity,
        ];
    }

    public function decreaseAndInsertInChunk($quantity)
    {
        $this->data [] = [
            'id' => $this->sku['id'],
            'product_id' => $this->sku['product_id'],
            'operation' => self::STOCK_DECREMENT,
            'quantity' => (float) $quantity,
        ];
    }
}
