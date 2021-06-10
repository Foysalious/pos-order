<?php namespace App\Services\OrderSku;

use App\Interfaces\OrderSkuRepositoryInterface;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Discount\Handler as DiscountHandler;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\WarrantyUnits;
use App\Services\Product\StockManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Creator
{
    private $order;
    /** @var OrderSkuRepositoryInterface */
    private OrderSkuRepositoryInterface $orderSkuRepository;
    /** @var DiscountHandler */
    private DiscountHandler $discountHandler;
    private array $skus;
    /** @var InventoryServerClient */
    private InventoryServerClient $client;

    /** @var StockManager $stockManager */
    private StockManager $stockManager;

    /**
     * Creator constructor.
     * @param OrderSkuRepositoryInterface $orderSkuRepository
     * @param DiscountHandler $discountHandler
     * @param InventoryServerClient $client
     */
    public function __construct(OrderSkuRepositoryInterface $orderSkuRepository, DiscountHandler $discountHandler, InventoryServerClient $client, StockManager $stockManager)
    {
        $this->orderSkuRepository = $orderSkuRepository;
        $this->discountHandler = $discountHandler;
        $this->client = $client;
        $this->stockManager = $stockManager;
    }


    /**
     * @param mixed $order
     * @return Creator
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param array $skus
     * @return Creator
     */
    public function setSkus(array $skus): Creator
    {
        $this->skus = $skus;
        return $this;
    }

    public function create()
    {
        $skus = $this->skus;
        $sku_ids = array_column($skus, 'id');
        $sku_ids = array_filter($sku_ids, function ($value) {
            return !is_null($value);
        });
        $sku_details = collect($this->getSkuDetails($sku_ids, $this->order->sales_channel_id))->keyBy('id')->toArray();
        foreach ($skus as $sku) {

            if ($sku->id != null && !isset($sku_details[$sku->id]))
                throw new NotFoundHttpException("Product #" . $sku->id . " Doesn't Exists.");

            $this->checkStockAvailability($sku,$sku_details);

            $sku_data['order_id'] = $this->order->id;
            $sku_data['name'] = isset($sku->product_name) ? $sku->product_name : $sku_details[$sku->id]['product_name'];
            $sku_data['sku_id'] = $sku->id ?: null;
            $sku_data['details'] = json_encode($sku);
            $sku_data['quantity'] = $sku->quantity;
            $sku_data['unit_price'] = isset($sku->price) ? $sku->price : $sku_details[$sku->id]['sku_channel'][0]['price'];
            $sku_data['unit'] = isset($sku->unit) ? $sku->unit : $sku_details[$sku->id]['unit']['name_en'];
            $sku_data['warranty'] = isset($sku->warranty) ? $sku->warranty : ($sku_details[$sku->id]['warranty'] ?: 0);
            $sku_data['warranty_unit'] = isset($sku->warranty_unit) ? $sku->warranty_unit : ($sku_details[$sku->id]['warranty'] ?: WarrantyUnits::DAY);
            $sku_data['vat_percentage'] = isset($sku->vat_percentage) ? $sku->vat_percentage : $sku_details[$sku->id]['vat_percentage'];
            $sku_data['discount']['discount'] = isset($sku->discount) ? $sku->discount : 0;
            $sku_data['discount']['is_discount_percentage'] = isset($sku->is_discount_percentage)  ? $sku->is_discount_percentage : null;
            $sku_data['discount']['cap'] = isset($sku->cap) ? $sku->cap : null;
            $order_sku = $this->orderSkuRepository->create($sku_data);
            $this->discountHandler->setType(DiscountTypes::SKU)->setOrder($this->order)->setSkuData($sku_data)->setOrderSkuId($order_sku->id);
            if ($this->discountHandler->hasDiscount()) {
                $this->discountHandler->create();
            }
            if(isset($sku_details[$sku->id])) {
                $is_stock_maintainable = $this->stockManager->setSku($sku_details[$sku->id])->setOrder($this->order)->isStockMaintainable();
                if ($is_stock_maintainable) $this->stockManager->decrease($sku->quantity);
            }
        }
    }

    private function getSkuDetails($sku_ids, $sales_channel_id)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&channel_id='.$sales_channel_id;
        $response = $this->client->get($url);
        return $response['skus'];
    }

    private function checkStockAvailability($sku, $sku_details)
    {
        if($sku->id == null || ($this->order->sales_channel_id == SalesChannelIds::POS)) return;
        if ($sku_details[$sku->id]['stock'] < $sku->quantity) throw new NotFoundHttpException("Product #" . $sku->id . " Not Enough Stock");
    }

}
