<?php namespace App\Services\Product;

use App\Models\Order;
use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannelIds;
use Exception;

class StockManager
{
    /** @var InventoryServerClient */
    private InventoryServerClient $client;
    private array $sku;
    private int $skuId;
    private int $partnerId;
    private array $data = [];

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

    /**
     * @param array $sku
     * @return StockManager
     */
    public function setSku(array $sku): static
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @param int $skuId
     * @return StockManager
     */
    public function setSkuId(int $skuId): static
    {
        $this->skuId = $skuId;
        return $this;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function isStockMaintainable(): bool
    {
        return !is_null($this->sku['stock']);
    }

    /**
     * @param int $partnerId
     * @return StockManager
     */
    public function setPartnerId(int $partnerId): StockManager
    {
        $this->partnerId = $partnerId;
        return $this;
    }


    /**
     * @param $quantity
     * @return void
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
        $this->client->put($this->getUri(),$data);
    }

    /**
     * @param $quantity
     * @return void
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
        $this->client->put($this->getUri(),$data);
    }

    public function increaseAndInsertInChunk($quantity)
    {
        $this->data [] = [
            'id' => $this->skuId,
            'operation' => self::STOCK_INCREMENT,
            'quantity' => (float) $quantity,
        ];
    }

    public function decreaseAndInsertInChunk($quantity)
    {
        $this->data [] = [
            'id' => $this->skuId,
            'operation' => self::STOCK_DECREMENT,
            'quantity' => (float) $quantity,
        ];
    }

    public function updateStock()
    {
        if($this->data){
            $this->client->put($this->getUri(),[ 'data' => json_encode($this->data) ]);
        }
    }

    private function getUri(): string
    {
        if (!$this->partnerId) throw new Exception('Partner id is not set');
        return 'api/v1/partners/' . $this->partnerId . '/stock-update';
    }
}
