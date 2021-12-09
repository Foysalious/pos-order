<?php namespace App\Services\Order\Refund;

use App\Interfaces\OrderDiscountRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\Order;
use App\Repositories\OrderSkuRepository;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Updater;
use App\Services\OrderSku\Creator;
use App\Services\Payment\Creator as PaymentCreator;
use App\Services\Product\StockManager;
use App\Traits\ModificationFields;
use Illuminate\Support\Collection;

abstract class ProductOrder
{
    use ModificationFields;
    /** @var Order */
    protected Order $order;

    /** @var Updater */
    protected Updater $updater;

    /** @var OrderSkuRepository  */
    protected OrderSkuRepository $orderSkuRepository;

    /** @var PaymentCreator  */
    protected PaymentCreator $paymentCreator;

    protected $data;

    protected Collection $skus;

    /** @var InventoryServerClient */
    protected InventoryServerClient $client;

    protected bool $isPaymentMethodEmi;


    /**
     * RefundProduct constructor.
     * @param Updater $updater
     */
    public function __construct(Updater $updater, OrderSkuRepositoryInterface $orderSkuRepository,
                                InventoryServerClient $client,
                                PaymentCreator $paymentCreator,
                                protected Creator $orderSkuCreator,
                                protected OrderDiscountRepositoryInterface $discountRepository
    )
    {
        $this->updater = $updater;
        $this->orderSkuRepository = $orderSkuRepository;
        $this->client = $client;
        $this->paymentCreator = $paymentCreator;
    }

    /**
     * @param Order $order
     * @return ProductOrder
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        $this->isPaymentMethodEmi = !is_null($this->order->emi_month);
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
        $response = $this->client->get($url);
        return $response['skus'];
    }

    /**
     * @return bool
     */
    public function isPaymentMethodEmi() : bool
    {
        return $this->isPaymentMethodEmi;
    }

    public abstract function update();

}
