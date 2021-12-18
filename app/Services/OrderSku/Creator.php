<?php namespace App\Services\OrderSku;

use App\Exceptions\OrderException;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Discount\Handler as DiscountHandler;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\WarrantyUnits;
use App\Services\Product\StockManager;
use App\Traits\ModificationFields;
use Illuminate\Validation\ValidationException;

class Creator
{
    use ModificationFields;
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

    private bool $isPaymentMethodEmi;
    private array $stockDecreasingData = [];

    /**
     * Creator constructor.
     * @param OrderSkuRepositoryInterface $orderSkuRepository
     * @param DiscountHandler $discountHandler
     * @param InventoryServerClient $client
     * @param StockManager $stockManager
     * @param BatchDetailCreator $batchDetailCreator
     */
    public function __construct(OrderSkuRepositoryInterface $orderSkuRepository, DiscountHandler $discountHandler,
                                InventoryServerClient $client, StockManager $stockManager,
                                protected BatchDetailCreator $batchDetailCreator)
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

    /**
     * @param bool $isPaymentMethodEmi
     * @return Creator
     */
    public function setIsPaymentMethodEmi(bool $isPaymentMethodEmi): Creator
    {
        $this->isPaymentMethodEmi = $isPaymentMethodEmi;
        return $this;
    }

    /**
     * @return array
     */
    public function getStockDecreasingData(): array
    {
        return $this->stockDecreasingData;
    }

    /**
     * @throws OrderException
     * @throws ValidationException
     */
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
        //product-wise emi for future
//        if($this->isPaymentMethodEmi) $this->checkEmiAvailabilityForProducts($skus, $sku_details);
        $this->checkProductAndStockAvailability($skus,$sku_details);
        foreach ($skus as $sku) {
            $sku_data['order_id'] = $this->order->id;
            $sku_data['name'] = $sku->product_name ?? $sku_details[$sku->id]['product_name'] ?? 'Custom Item';
            $sku_data['sku_id'] = $sku->id ?: null;
            $sku_data['details'] = isset($sku_details[$sku->id]) && $sku_details[$sku->id]['combination'] ? json_encode($sku_details[$sku->id]['combination']) : null;
            $sku_data['batch_detail'] = $this->makeBatchDetail($sku,$sku_details[$sku->id] ?? null);
            $sku_data['quantity'] = $sku->quantity;
            $sku_data['unit_weight'] = isset($sku_details[$sku->id]) && $sku_details[$sku->id]['weight'] ? $sku_details[$sku->id]['weight'] : null ;
            $sku_data['unit_price'] = $sku->price ?? $sku_details[$sku->id]['sku_channel'][0]['price'];
            $sku_data['unit'] = $sku->unit ?? (isset($sku_details[$sku->id]) ? ($sku_details[$sku->id]['unit']['name_en'] ?? null) : null);
            $sku_data['warranty'] = $sku->warranty ?? (isset($sku_details[$sku->id]) && $sku_details[$sku->id]['warranty'] ?: 0);
            $sku_data['warranty_unit'] = $sku->warranty_unit ?? (isset($sku_details[$sku->id]) && $sku_details[$sku->id]['warranty'] ?: WarrantyUnits::DAY);
            $sku_data['vat_percentage'] = $sku->vat_percentage ?? (isset($sku_details[$sku->id]) ? $sku_details[$sku->id]['vat_percentage'] : 0);
            $sku_data['product_image'] = $sku_details[$sku->id]['app_thumb'] ?? null;
            $sku_data['note'] = $sku->note ?? null;
            $sku_data['discount'] = $this->resolveDiscount($sku,$sku_details[$sku->id] ?? null);
            $sku_data['is_emi_available'] = $this->isPaymentMethodEmi;
            $order_sku = $this->orderSkuRepository->create($this->withCreateModificationField($sku_data));
            $created_skus [] = $order_sku;
            $this->discountHandler->setType(DiscountTypes::ORDER_SKU)->setOrder($this->order)->setSkuData($sku_data)->setOrderSkuId($order_sku->id);
            if ($this->discountHandler->hasDiscount()) {
                $this->discountHandler->create();
            }
            if(isset($sku_details[$sku->id])) {
                    $this->stockDecreasingData [] = [
                        'sku_detail' => $sku_details[$sku->id],
                        'quantity' => (float) $sku->quantity,
                        'operation' => StockManager::STOCK_DECREMENT
                    ];
            }
        }
        return $created_skus;
    }

    private function getSkuDetails($sku_ids, $sales_channel_id)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&channel_id='.$sales_channel_id;
        $response = $this->client->get($url);
        return $response['skus'];
    }

    /**
     * @throws OrderException
     */
    private function checkProductAndStockAvailability($skus, $sku_details)
    {
        foreach ($skus as $sku) {
            if ($sku->id != null && !isset($sku_details[$sku->id]))
                throw new OrderException("Product #" . $sku->id . " Doesn't Exists.");
            if($sku->id == null || ($this->order->sales_channel_id == SalesChannelIds::POS))
                continue;
            if ($sku_details[$sku->id]['stock'] < $sku->quantity)
                throw new OrderException("Product #" . $sku->id . " Not Enough Stock");
        }
    }

    private function makeBatchDetail(object $sku, ?array $sku_details) : null | string
    {
        if ( is_null($sku_details)) {
           return null;
        } else {
            $data = $this->batchDetailCreator->setSku($sku)->setSkuDetails($sku_details)->create();
            return json_encode($data);
        }
    }

    // product-wise emi for future
    /*
     private function checkEmiAvailabilityForProducts(array $skus, array $sku_details)
    {
        if($this->order->sales_channel_id == SalesChannelIds::POS) return;
        foreach ($skus as $sku) {
            if(!is_null($sku->id)) {
                $sku_detail = $sku_details[$sku->id];
                $emi_availability = $sku_detail['sku_channel'][0]['is_emi_available'] ?? false;
                if ($emi_availability == false) {
                    throw new OrderException("Emi is not available for Product #" . $sku->id, 400);
                }
            } else {
                if($sku->price < config('emi.minimum_emi_amount')) {
                    throw new OrderException("Emi is not available for quick sell amount " . $sku->price, 400);
                }
            }
        }
    }
    */

    private function resolveDiscount(object $sku, array|null $sku_detail)
    {
        $discount_detail = collect($sku_detail['sku_channel'] ?? [])?->pluck('valid_discounts')?->collapse()?->first();
        $discount_data = [
            'discount' => 0,
            'is_discount_percentage' => 0,
            'cap' => null,
        ];
        if($this->order->sales_channel_id == SalesChannelIds::POS) {
            $discount_data = [
                'discount' => $sku->discount ?? $discount_detail['amount'] ?? 0,
                'is_discount_percentage' => $sku->is_discount_percentage ?? $discount_detail['is_amount_percentage'] ?? 0,
                'cap' => $sku->cap ?? $discount_detail['cap'] ?? 0,
            ];
        } else {

            if($discount_detail){
                $discount_data = [
                    'discount' => $discount_detail['amount'],
                    'is_discount_percentage' => $discount_detail['is_amount_percentage'],
                    'cap' => $discount_detail['cap']
                ];
            }
        }
        return $discount_data;
    }
}
