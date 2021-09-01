<?php namespace App\Services\Product;

use App\Models\Order;
use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannelIds;

class StockManager
{
    /** @var InventoryServerClient */
    protected InventoryServerClient $client;
    protected $sku;
    protected Order $order;
    protected string $uri = 'api/v1/partners/{partner_id}/stock-update';
    protected array $data = [];

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
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        $this->setPartnerInUri();
        return $this;
    }

    public function isStockMaintainable()
    {
        return !is_null($this->sku['stock']);
    }

    /**
     * @param $quantity
     * @return false|mixed
     * @throws BaseClientServerError
     */
    public function increase($quantity)
    {
        $data = [
            'id' => $this->sku['id'],
            'product_id' => $this->sku['product_id'],
            'operation' => self::STOCK_INCREMENT,
            'quantity' => $quantity
        ];
        $this->client->setBaseUrl()->put($this->uri,$data);
    }

    /**
     * @param $quantity
     * @return false|mixed
     * @throws BaseClientServerError
     */
    public function decrease($quantity)
    {
        $data = [
            'id' => $this->sku['id'],
            'product_id' => $this->sku['product_id'],
            'operation' => self::STOCK_DECREMENT,
            'quantity' => $quantity
        ];
        $this->client->setBaseUrl()->put($this->uri,$data);
    }
}
