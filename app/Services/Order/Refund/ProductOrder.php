<?php namespace App\Services\Order\Refund;

use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\Order;
use App\Repositories\OrderSkuRepository;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Updater;
use Illuminate\Support\Collection;

abstract class ProductOrder
{
    /** @var Order */
    protected Order $order;

    /** @var Updater */
    protected Updater $updater;

    /** @var OrderSkuRepository  */
    protected OrderSkuRepository $orderSkuRepository;

    protected $data;

    protected Collection $skus;

    /** @var InventoryServerClient */
    protected InventoryServerClient $client;


    /**
     * RefundProduct constructor.
     * @param Updater $updater
     */
    public function __construct(Updater $updater, OrderSkuRepositoryInterface $orderSkuRepository, InventoryServerClient $client)
    {
        $this->updater = $updater;
        $this->orderSkuRepository = $orderSkuRepository;
        $this->client = $client;
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

    public abstract function update();

}
