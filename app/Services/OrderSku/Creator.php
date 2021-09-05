<?php namespace App\Services\OrderSku;

use App\Interfaces\OrderSkuRepositoryInterface;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Discount\Handler as DiscountHandler;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\WarrantyUnits;
use App\Services\Product\StockManager;
use Illuminate\Support\Facades\App;
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
        $created_skus = [];
        $skus = $this->skus;
        $sku_ids = array_column($skus, 'id');
        $sku_ids = array_filter($sku_ids, function ($value) {
            return !is_null($value);
        });
        $sku_details = [];
        if(count($sku_ids) > 0){
            $sku_details = collect($this->getSkuDetails($sku_ids, $this->order->sales_channel_id))->keyBy('id')->toArray();
        }
        $this->checkProductAndStockAvailability($skus,$sku_details);
        foreach ($skus as $sku) {
            $sku_data['order_id'] = $this->order->id;
            $sku_data['name'] = $sku->product_name ?? $sku_details[$sku->id]['product_name'] ?? 'Quick Sell Item';
            $sku_data['sku_id'] = $sku->id ?: null;
            $sku_data['details'] = $this->makeSkudetails($sku,$sku_details[$sku->id] ?? null);
            $sku_data['quantity'] = $sku->quantity;
            $sku_data['unit_price'] = $sku->price ?? $sku_details[$sku->id]['sku_channel'][0]['price'];
            $sku_data['unit'] = $sku->unit ?? (isset($sku_details[$sku->id]) ? ($sku_details[$sku->id]['unit']['name_en'] ?? null) : null);
            $sku_data['warranty'] = $sku->warranty ?? (isset($sku_details[$sku->id]) && $sku_details[$sku->id]['warranty'] ?: 0);
            $sku_data['warranty_unit'] = $sku->warranty_unit ?? (isset($sku_details[$sku->id]) && $sku_details[$sku->id]['warranty'] ?: WarrantyUnits::DAY);
            $sku_data['vat_percentage'] = $sku->vat_percentage ?? (isset($sku_details[$sku->id]) ? $sku_details[$sku->id]['vat_percentage'] : 0);
            $sku_data['discount']['discount'] = $sku->discount ?? 0;
            $sku_data['discount']['is_discount_percentage'] = $sku->is_discount_percentage ?? null;
            $sku_data['discount']['cap'] = $sku->cap ?? null;
            $order_sku = $this->orderSkuRepository->create($sku_data);
            $created_skus [] = $order_sku;
            $this->discountHandler->setType(DiscountTypes::SKU)->setOrder($this->order)->setSkuData($sku_data)->setOrderSkuId($order_sku->id);
            if ($this->discountHandler->hasDiscount()) {
                $this->discountHandler->create();
            }
            if(isset($sku_details[$sku->id])) {
                $is_stock_maintainable = $this->stockManager->setSku($sku_details[$sku->id])->setOrder($this->order)->isStockMaintainable();
                if ($is_stock_maintainable) $this->stockManager->decrease($sku->quantity);
            }
        }
        return $created_skus;
    }

    private function getSkuDetails($sku_ids, $sales_channel_id)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&channel_id='.$sales_channel_id;
        $response = $this->client->setBaseUrl()->get($url);
        return $response['skus'];
    }

    private function checkProductAndStockAvailability($skus, $sku_details)
    {
        foreach ($skus as $sku) {
            if ($sku->id != null && !isset($sku_details[$sku->id]))
                throw new NotFoundHttpException("Product #" . $sku->id . " Doesn't Exists.");
            if($sku->id == null || ($this->order->sales_channel_id == SalesChannelIds::POS))
                continue;
            if ($sku_details[$sku->id]['stock'] < $sku->quantity)
                throw new NotFoundHttpException("Product #" . $sku->id . " Not Enough Stock");
        }
    }

    private function makeSkudetails(object $sku, ?array $sku_details)
    {
        if ( is_null($sku_details)) {
           return json_encode($sku);
        } else {
            /** @var OrderSkuDetailCreator $creator */
            $creator = App::make(OrderSkuDetailCreator::class);
            $data = $creator->setSku($sku)->setSkuDetails($sku_details)->create();
            return json_encode($data);
        }
    }
}
