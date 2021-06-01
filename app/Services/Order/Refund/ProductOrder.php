<?php


namespace App\Services\Order\Refund;


use App\Models\Order;
use App\Services\Order\Updater;
use Illuminate\Support\Collection;

abstract class ProductOrder
{
    /** @var Order */
    public Order $order;

    /** @var Updater */
    public Updater $updater;

    public array $data;

    public Collection $skus;

    /**
     * RefundProduct constructor.
     * @param Updater $updater
     */
    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
    }

    /**
     * @param Order $order
     * @return RefundProduct
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param array $data
     * @return RefundProduct
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->skus = $this->setSkus();
        return $this;
    }


    public function setSkus(): Collection
    {
        return collect(json_decode($this->data['skus']));
    }

    public abstract function update();

}
