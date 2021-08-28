<?php namespace App\Services\Accounting;

use App\Helper\Miscellaneous\RequestIdentification;
use App\Models\Order;
use App\Repositories\Accounting\AccountingRepository;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\Accounting\Constants\Accounts;
use App\Services\Accounting\Constants\Cash;
use App\Services\Accounting\Constants\OrderChangingTypes;
use App\Services\Accounting\Constants\Sales;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannel;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\PriceCalculation;

use App\Services\Order\Refund\Objects\AddRefundTracker;
use Illuminate\Support\Facades\App;

class UpdateEntry extends BaseEntry
{
    protected array $orderProductChangeData;
    const NEWLY_ADDED_PRODUCT = 'new';
    const FULLY_DELETED_PRODUCT = 'deleted';
    const QUANTITY_INCREASED = 'increased';
    const QUANTITY_DECREASED = 'decreased';
    /**
     * UpdateEntry constructor.
     */
    public function __construct(AccountingRepository $accountingRepository, InventoryServerClient $client)
    {
        parent::__construct($accountingRepository, $client);
    }

    public function update()
    {
        $data = $this->makeData();
        $this->accountingRepository->updateEntryBySource($data, $this->order->id, $this->order->partner_id);
    }

    /**
     * @param array $orderProductChangeData
     * @return UpdateEntry
     */
    public function setOrderProductChangeData(array $orderProductChangeData)
    {
        $this->orderProductChangeData = $orderProductChangeData;
        return $this;
    }

    private function makeData()
    {
        /** @var PriceCalculation $order_price_details */
        $order_price_details = $this->getOrderPriceDetails();

        $customer = $this->order->customer->only('id','name');
        $inventory_products = $this->makeInventoryProducts();
        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => Sales::SALES_FROM_POS,
            'debit_account_key'  => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id'          => $this->order->id,
            'source_type'        => EntryTypes::POS,
            'note'               => $this->getNote(),
            'amount'             => $order_price_details->getDiscountedPrice(),
            'amount_cleared'     => (float) $this->orderProductChangeData['paid_amount'],
            'reconcile_amount'   => (float) $this->calculateAmountChange($inventory_products),
            'total_discount'     => $order_price_details->getDiscount(),
            'total_vat'          => $order_price_details->getVat(),
            'entry_at' => convertTimezone($this->order->created_at)->format('Y-m-d H:i:s'),
            'inventory_products' => json_encode($inventory_products),
            'customer_id' => $customer['id'],
            'customer_name' => $customer['name'],
        ];
        return $data;

    }

    private function makeInventoryProducts()
    {
        $data = [];
        $sku_ids = $this->getSkuIdsFromProductChangeData();
        $sku_ids_filtered = collect($sku_ids)->flatten()->whereNotNull()->toArray();
        $sku_details = collect($this->getSkuDetails($sku_ids_filtered,$this->order->sales_channel_id))->keyBy('id');
        $order_skus = $this->order->orderSkus()->withTrashed()->get();

        if(isset($this->orderProductChangeData['new'])){
            $data = array_merge_recursive($this->makeNewAndDeletedProductsData($order_skus,$sku_details, self::NEWLY_ADDED_PRODUCT), $data);
        }
        if(isset($this->orderProductChangeData['deleted']['refunded_products'])) {
            $data = array_merge_recursive($this->makeNewAndDeletedProductsData($order_skus,$sku_details, self::FULLY_DELETED_PRODUCT), $data);
        }
        $data = array_merge_recursive($this->makeRefundExchangedProductsData($order_skus,$sku_details), $data);
        return $data;

    }

    private function makeNewAndDeletedProductsData($order_skus, $sku_details, $product_type)
    {
        $data = [];
        if($product_type == self::NEWLY_ADDED_PRODUCT) {
            $items = $this->orderProductChangeData['new'];
        } elseif ($product_type == self::FULLY_DELETED_PRODUCT) {
            $items = $this->orderProductChangeData['deleted']['refunded_products'];
        } else {
            return [];
        }
        foreach ($items as $item) {
            $order_sku = $order_skus->where('id', $item['id'])->first();
            if ($item['id'] != null) {
                $sku_id = $item['sku_id'];
                $batch_wise_skus = $this->splitSkuByBatch($order_sku);
                foreach ($batch_wise_skus as $batch) {
                    $data [] = [
                        'id' => $sku_details[$sku_id]['product_id'],
                        'sku_id' => $sku_details[$sku_id]['id'],
                        'name' => $sku_details[$sku_id]['name'] ?? '',
                        "unit_price" => (double) $batch['unit_price'],
                        "selling_price" => (double) $order_sku->unit_price,
                        "quantity" => (double) $batch['quantity'],
                        "type" => $product_type == self::NEWLY_ADDED_PRODUCT ? 'new' : OrderChangingTypes::REFUND,
                    ];
                }

            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => (double) $item['unit_price'],
                    "selling_price" => (double) $item['unit_price'],
                    "quantity" => (double) $item['quantity'],
                    "type" =>  $product_type == self::NEWLY_ADDED_PRODUCT ? 'new' : OrderChangingTypes::REFUND,
                ];
            }
        }
        return $data;
    }

    private function makeRefundExchangedProductsData($order_skus, $sku_details)
    {
        $data = [];
        $added_items = $this->orderProductChangeData['refund_exchanged']['added_products'];
        $refunded_items = $this->orderProductChangeData['refund_exchanged']['refunded_products'];
        /** @var AddRefundTracker $item */
        foreach ($added_items as $item) {
            $order_sku = $order_skus->where('id', $item->getOrderSkuId())->first();
            $sku_id = $item->getSkuId();

            if ($item->getSkuId() != null) {
                $batch_wise_cost = $this->getBatchWiseCost($item, self::QUANTITY_INCREASED);
                foreach ($batch_wise_cost as $batch) {
                    $data [] = [
                        'id' => $sku_details[$sku_id]['product_id'],
                        'sku_id' => $sku_details[$sku_id]['id'],
                        'name' => $sku_details[$sku_id]['name'] ?? '',
                        "unit_price" => (double) $batch['unit_price'],
                        "selling_price" => (double) $order_sku->unit_price,
                        "quantity" => (double) $batch['quantity'],
                        "type" => OrderChangingTypes::QUANTITY_INCREASE
                    ];
                }
            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => (double) $order_sku->unit_price,
                    "selling_price" => (double) $order_sku->unit_price,
                    "quantity" => $item->getQuantityChangedValue(),
                    "type" =>  OrderChangingTypes::QUANTITY_INCREASE,
                ];
            }
        }

        /** @var AddRefundTracker $item */
        foreach ($refunded_items as $item) {
            $order_sku = $order_skus->where('id', $item->getOrderSkuId())->first();
            $sku_id = $item->getSkuId();

            if ($item->getSkuId() != null) {
                $batch_wise_cost = $this->getBatchWiseCost($item, self::QUANTITY_DECREASED);
                foreach ($batch_wise_cost as $batch) {
                    $data [] = [
                        'id' => $sku_details[$sku_id]['product_id'],
                        'sku_id' => $sku_details[$sku_id]['id'],
                        'name' => $sku_details[$sku_id]['name'] ?? '',
                        "unit_price" => (double) $batch['unit_price'],
                        "selling_price" => (double) $order_sku->unit_price,
                        "quantity" => (double) $batch['quantity'],
                        "type" => OrderChangingTypes::REFUND
                    ];
                }
            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => (double) $order_sku->unit_price,
                    "selling_price" => (double) $order_sku->unit_price,
                    "quantity" => $item->getQuantityChangedValue(),
                    "type" =>  OrderChangingTypes::REFUND,
                ];
            }
        }
        return $data;
    }

    private function calculateAmountChange($data)
    {
        $amount = 0;
        foreach ($data as $each) {
            if($each['type'] == OrderChangingTypes::QUANTITY_INCREASE)$amount = $amount + ($each['quantity']*$each['selling_price']);
            if($each['type'] == OrderChangingTypes::NEW) $amount = $amount + ($each['quantity']*$each['selling_price']);
            if($each['type'] == OrderChangingTypes::REFUND) $amount = $amount - ($each['quantity']*$each['selling_price']);
        }
        return $amount;
    }

    private function getNote()
    {
        $note = '';
        if(count($this->orderProductChangeData['new'] ?? []) > 0) $note .= OrderChangingTypes::NEW . '-' ;
        if(count($this->orderProductChangeData['deleted'] ?? []) > 0) $note .= OrderChangingTypes::REFUND .'-';
        if(count($this->orderProductChangeData['refund_exchanged']['added_products'] ?? []) > 0) $note .=  OrderChangingTypes::QUANTITY_INCREASE . '-';
        if(count($this->orderProductChangeData['refund_exchanged']['refunded_products'] ?? []) > 0) $note .=  OrderChangingTypes::REFUND;
        return $note;
    }

    private function getSkuIdsFromProductChangeData()
    {
        $sku_ids [] = array_column($this->orderProductChangeData['new'] ?? [], 'sku_id');
        $sku_ids [] = array_column($this->orderProductChangeData['deleted']['refunded_products'] ?? [], 'sku_id');
        $quantity_added = $this->orderProductChangeData['refund_exchanged']['added_products'];
        array_walk($quantity_added, function ($item) use (&$sku_ids){
           $sku_ids [] = $item->getSkuId();
        });
        $refunded = $this->orderProductChangeData['refund_exchanged']['refunded_products'];
        array_walk($refunded, function ($item) use (&$sku_ids){
            $sku_ids [] = $item->getSkuId();
        });
        return $sku_ids;
    }

    private function splitSkuByBatch($order_sku)
    {
        $order_details = json_decode($order_sku->details, true);
        $batch_detail = $order_details['batch_detail'] ?? [];
        $data = [];
        if (empty($batch_detail)) {
            $data [] = [
                'quantity' => $order_sku->quantity,
                'unit_price' => $order_sku->unit_price,
            ];
        } else {
            foreach ($batch_detail as $batch) {
                $data [] = [
                    'quantity' => $batch['quantity'],
                    'unit_price' => $batch['cost'],
                ];
            }
        }
        return $data;

    }

    private function getBatchWiseCost(AddRefundTracker $item, $for)
    {
        $batch_detail = $item->getUpdatedBatchDetail();
        if($for == self::QUANTITY_DECREASED){
            $batch_detail = $item->getOldBatchDetail();
        }


        if (empty($batch_detail)) {
            $data [] = [
                'quantity' => $item->getQuantityChangedValue(),
                'unit_price' => $item->getOldUnitPrice(),
            ];
        } else {
            $batch_detail = collect($batch_detail)->sortByDesc('batch_id');
            if($for == self::QUANTITY_INCREASED) {
                $increase = $item->getQuantityChangedValue();
                $data = $this->takeOutFromBatchDetail($increase, $batch_detail );
            } else {
                $decrease = $item->getQuantityChangedValue();
                $data = $this->takeOutFromBatchDetail($decrease, $batch_detail );
            }
        }
        return $data;

    }

    private function takeOutFromBatchDetail($quantity, $batch_detail)
    {
        foreach ($batch_detail as $batch) {
            if($batch['quantity'] >= $quantity){
                $data [] = [
                    'quantity' => $quantity,
                    'unit_price' => $batch['cost'],
                ];
                break;
            } elseif ($batch['quantity'] < $quantity) {
                $data [] = [
                    'quantity' => $batch['quantity'],
                    'unit_price' => $batch['cost'],
                ];
                $quantity = $quantity - $batch['quantity'];
            }
        }
        return $data;

    }

}
