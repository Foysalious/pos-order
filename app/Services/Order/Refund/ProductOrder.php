<?php namespace App\Services\Order\Refund;

use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\Order;
use App\Repositories\OrderSkuRepository;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Updater;
use App\Services\Order\Payment\Creator as OrderPaymentCreator;
use App\Services\Product\StockManager;
use Illuminate\Support\Collection;

abstract class ProductOrder
{
    /** @var Order */
    protected Order $order;

    /** @var Updater */
    protected Updater $updater;

    /** @var OrderSkuRepository  */
    protected OrderSkuRepository $orderSkuRepository;

    /** @var OrderPaymentCreator  */
    protected OrderPaymentCreator $orderPaymentCreator;

    protected $data;

    protected Collection $skus;

    /** @var InventoryServerClient */
    protected InventoryServerClient $client;

    /** @var StockManager $stockManager */
    protected StockManager $stockManager;


    /**
     * RefundProduct constructor.
     * @param Updater $updater
     */
    public function __construct(Updater $updater, OrderSkuRepositoryInterface $orderSkuRepository, InventoryServerClient $client, StockManager $stockManager, OrderPaymentCreator $orderPaymentCreator)
    {
        $this->updater = $updater;
        $this->orderSkuRepository = $orderSkuRepository;
        $this->client = $client;
        $this->stockManager = $stockManager;
        $this->orderPaymentCreator = $orderPaymentCreator;
    }

    /**
     * @param Order $order
     * @return ProductOrder
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return ProductOrder
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->skus = $this->setSkus();
        return $this;
    }


    public function setSkus(): Collection
    {
        return collect(json_decode($this->data));
    }

    protected function getSkuDetails($sku_ids, $sales_channel_id)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&channel_id='.$sales_channel_id;
        $response = $this->client->setBaseUrl()->get($url);
        return $response['skus'];
    }

    public abstract function update();

}
