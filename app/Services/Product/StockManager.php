<?php namespace App\Services\Product;

use App\Models\Order;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannelIds;

class StockManager
{
    /** @var InventoryServerClient */
    private InventoryServerClient $client;
    private $sku;
    private Order $order;
    private string $uri = 'api/v1/partners/{partner_id}/stock-update';

    const STOCK_INCREMENT = 'increment';
    const STOCK_DECREMENT = 'decrement';

    /**
     * StockManager constructor.
     * @param InventoryServerClient $client
     */
    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    private function setPartnerInUri(){
        $this->uri = str_replace('{partner_id}', $this->order->partner_id, $this->uri);
    }
    /**
     * @param array $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        $this->setPartnerInUri();
        return $this;
    }

    public function isStockMaintainable()
    {
        return !($this->sku['stock'] == 0);
    }

    /**
     * @param $quantity |double
     */
    public function increase($quantity)
    {
        $data = [
            'id' => $this->sku['id'],
            'product_id' => $this->sku['product_id'],
            'operation' => self::STOCK_INCREMENT,
            'quantity' => $quantity
        ];
        $response = $this->client->put($this->uri,$data);
    }

    /**
     * @param $quantity |double
     */
    public function decrease($quantity)
    {
        if (($this->sku['stock'] - $quantity < 0) && $this->order->sales_channel_id == SalesChannelIds::POS )
            $quantity = $this->sku['stock'];
        $data = [
            'id' => $this->sku['id'],
            'product_id' => $this->sku['product_id'],
            'operation' => self::STOCK_DECREMENT,
            'quantity' => $quantity
        ];
        $response = $this->client->put($this->uri,$data);
    }
}
