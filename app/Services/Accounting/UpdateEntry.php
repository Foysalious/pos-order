<?php namespace App\Services\Accounting;

use App\Helper\Miscellaneous\RequestIdentification;
use App\Models\Customer;
use App\Repositories\Accounting\AccountingRepository;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\Accounting\Constants\Accounts;
use App\Services\Accounting\Constants\Cash;
use App\Services\Accounting\Constants\OrderChangingTypes;
use App\Services\Accounting\Constants\Sales;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\PriceCalculation;
use App\Services\Order\Refund\Objects\AddRefundTracker;
use Exception;
use Illuminate\Support\Collection;

class UpdateEntry extends BaseEntry
{
    protected array $orderProductChangeData;
    private array $previousOrderData;
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

    /**
     * @throws Exception
     */
    public function update()
    {
        $this->getNotifier()->updateEntryBySource($this->makeData(), $this->order->id, $this->order->partner_id);
    }

    /**
     * @param array $orderProductChangeData
     * @return UpdateEntry
     */
    public function setOrderProductChangeData(array $orderProductChangeData): static
    {
        $this->orderProductChangeData = $orderProductChangeData;
        return $this;
    }

    /**
     * @param array $previousOrderData
     * @return UpdateEntry
     */
    public function setPreviousOrderData(array $previousOrderData): UpdateEntry
    {
        $this->previousOrderData = $previousOrderData;
        return $this;
    }

    private function makeData(): array
    {
        $order_price_details = $this->getOrderPriceDetails(new PriceCalculation());

        $customer = Customer::where('id', $this->order->customer_id)->where('partner_id', $this->order->partner_id)->first();
        $inventory_products = $this->makeInventoryProducts();
        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => Sales::SALES_FROM_POS,
            'debit_account_key' => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id' => $this->order->id,
            'source_type' => EntryTypes::POS,
            'note' => $this->getNote(),
            'amount' => round(($order_price_details->getOriginalPrice() + $order_price_details->getVat()), 2, PHP_ROUND_HALF_UP),
            'amount_cleared' => $order_price_details->getPaid(),
            'reconcile_amount' => (float)$this->calculateAmountChange($inventory_products),
            'total_discount' => $order_price_details->getDiscount(),
            'total_vat' => $order_price_details->getVat(),
            'updated_discount' => (double) ($order_price_details->getDiscount() - $this->previousOrderData['discount']),
            'updated_vat' => (double) ($order_price_details->getVat() - $this->previousOrderData['vat']),
            'entry_at' => convertTimezone($this->order->created_at)?->format('Y-m-d H:i:s'),
            'delivery_charge' => (double)$this->order->delivery_charge ?? 0,
            'updated_delivery_charge' => (double) ($this->order->delivery_charge - $this->previousOrderData['delivery_charge']) ?? 0,
            'bank_transaction_charge' => (double)$this->order->bank_transaction_charge ?? 0,
            'interest' => (double)$this->order->interest ?? 0,
            'updated_entry' => 0,
            'inventory_products' => json_encode($inventory_products),
        ];
        return array_merge($data, $this->makeCustomerData($customer));
    }

    private function makeInventoryProducts(): array
    {
        $data = [];
        $sku_ids = $this->getSkuIdsFromProductChangeData();
        $sku_ids_filtered = collect($sku_ids)->flatten()->whereNotNull()->toArray();
        /** @var Collection $sku_details */
        $sku_details = $sku_ids_filtered ? collect($this->getSkuDetails($sku_ids_filtered, $this->order->sales_channel_id))->keyBy('id') : collect();
        $order_skus = $this->order->orderSkus()->withTrashed()->get();

        if (isset($this->orderProductChangeData['new'])) {
            $data = array_merge_recursive($this->makeNewAndDeletedProductsData($order_skus, $sku_details, self::NEWLY_ADDED_PRODUCT), $data);
        }
        if (isset($this->orderProductChangeData['deleted']['refunded_products'])) {
            $data = array_merge_recursive($this->makeNewAndDeletedProductsData($order_skus, $sku_details, self::FULLY_DELETED_PRODUCT), $data);
        }
        return array_merge_recursive($this->makeRefundExchangedProductsData($order_skus, $sku_details), $data);

    }

    private function makeNewAndDeletedProductsData($order_skus, $sku_details, $product_type): array
    {
        $data = [];
        if ($product_type == self::NEWLY_ADDED_PRODUCT) {
            $items = $this->orderProductChangeData['new'];
        } elseif ($product_type == self::FULLY_DELETED_PRODUCT) {
            $items = $this->orderProductChangeData['deleted']['refunded_products'];
        } else {
            return [];
        }
        foreach ($items as $item) {
            $order_sku = $order_skus->where('id', $item['id'])->first();
            if ($item['sku_id'] != null) {
                $sku_id = $item['sku_id'];
                $batch_wise_skus = $this->splitSkuByBatch($order_sku);
                foreach ($batch_wise_skus as $batch) {
                    $data [] = [
                        'id' => $sku_details[$sku_id]['product_id'] ?? 0,
                        'sku_id' => $sku_details[$sku_id]['id'],
                        'name' => $sku_details[$sku_id]['name'] ?? '',
                        "unit_price" => (double)$batch['unit_price'],
                        "selling_price" => (double)$order_sku->unit_price,
                        "quantity" => (double)$batch['quantity'],
                        "type" => $product_type == self::NEWLY_ADDED_PRODUCT ? 'new' : OrderChangingTypes::REFUND,
                    ];
                }

            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => 0,
                    "selling_price" => (double)$item['unit_price'],
                    "quantity" => (double)$item['quantity'],
                    "type" => $product_type == self::NEWLY_ADDED_PRODUCT ? 'new' : OrderChangingTypes::REFUND,
                ];
            }
        }
        return $data;
    }

    private function makeRefundExchangedProductsData(\Illuminate\Database\Eloquent\Collection $order_skus, Collection $sku_details): array
    {
        $data = [];
        $added_items = $this->orderProductChangeData['refund_exchanged']['added_products'] ?? [];
        $refunded_items = $this->orderProductChangeData['refund_exchanged']['refunded_products'] ?? [];
        if (count($added_items) > 0) {
            $data = array_merge_recursive($this->makeAddedRefundedProductsData($added_items, $order_skus, $sku_details), $data);
        }
        if (count($refunded_items) > 0) {
            $data = array_merge_recursive($this->makeAddedRefundedProductsData($refunded_items, $order_skus, $sku_details), $data);
        }
        return $data;
    }

    private function calculateAmountChange($data)
    {
        $amount = 0;
        foreach ($data as $each) {
            if ($each['type'] == OrderChangingTypes::QUANTITY_INCREASE) $amount = $amount + ($each['quantity'] * $each['selling_price']);
            if ($each['type'] == OrderChangingTypes::NEW) $amount = $amount + ($each['quantity'] * $each['selling_price']);
            if ($each['type'] == OrderChangingTypes::REFUND) $amount = $amount - ($each['quantity'] * $each['selling_price']);
        }
        return $amount;
    }

    private function getNote(): string
    {
        $note = '';
        if (count($this->orderProductChangeData['new'] ?? []) > 0) {
            $note = OrderChangingTypes::EXCHANGE;
        } else if (count($this->orderProductChangeData['deleted'] ?? []) > 0) {
            $note = OrderChangingTypes::REFUND;
        } else if (count($this->orderProductChangeData['refund_exchanged']['added_products'] ?? []) > 0) {
            $note = OrderChangingTypes::EXCHANGE;
        } else if (count($this->orderProductChangeData['refund_exchanged']['refunded_products'] ?? []) > 0) {
            $note = OrderChangingTypes::REFUND;
        }
        return $note;
    }

    private function getSkuIdsFromProductChangeData(): array
    {
        $sku_ids [] = array_column($this->orderProductChangeData['new'] ?? [], 'sku_id');
        $sku_ids [] = array_column($this->orderProductChangeData['deleted']['refunded_products'] ?? [], 'sku_id');
        $quantity_added = $this->orderProductChangeData['refund_exchanged']['added_products'] ?? [];
        array_walk($quantity_added, function ($item) use (&$sku_ids) {
            $sku_ids [] = $item->getSkuId();
        });
        $refunded = $this->orderProductChangeData['refund_exchanged']['refunded_products'] ?? [];
        array_walk($refunded, function ($item) use (&$sku_ids) {
            $sku_ids [] = $item->getSkuId();
        });
        return $sku_ids;
    }

    private function splitSkuByBatch($order_sku): array
    {
        $batch_detail = json_decode($order_sku->batch_detail, true);
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

    private function getBatchWiseCost(AddRefundTracker $item, string $quantity_change_type): array
    {
        $batch_detail = $item->getUpdatedBatchDetail();
        if ($quantity_change_type == self::QUANTITY_DECREASED) {
            $batch_detail = $item->getOldBatchDetail();
        }


        if (empty($batch_detail)) {
            $data [] = [
                'quantity' => $item->getQuantityChangedValue(),
                'unit_price' => $item->getOldUnitPrice(),
            ];
        } else {
            $batch_detail = collect($batch_detail)->sortByDesc('batch_id');
            if ($quantity_change_type == self::QUANTITY_INCREASED) {
                $increase = $item->getQuantityChangedValue();
                $data = $this->takeOutFromBatchDetail($increase, $batch_detail);
            } else {
                $decrease = $item->getQuantityChangedValue();
                $data = $this->takeOutFromBatchDetail($decrease, $batch_detail);
            }
        }
        return $data;

    }

    private function takeOutFromBatchDetail($quantity, $batch_detail): array
    {
        $data = [];
        foreach ($batch_detail as $batch) {
            if ($batch['quantity'] >= $quantity) {
                $data [] = [
                    'quantity' => $quantity,
                    'unit_price' => $batch['cost'],
                ];
                break;
            } else {
                $data [] = [
                    'quantity' => $batch['quantity'],
                    'unit_price' => $batch['cost'],
                ];
                $quantity = $quantity - $batch['quantity'];
            }
        }
        return $data;

    }

    public function makeAddedRefundedProductsData(array $products, \Illuminate\Database\Eloquent\Collection $order_skus, Collection $sku_details): array
    {
        $data = [];
        /** @var AddRefundTracker $each_product */
        foreach ($products as $each_product) {
            $order_sku = $order_skus->where('id', $each_product->getOrderSkuId())->first();
            $sku_id = $each_product->getSkuId();

            if ($each_product->getSkuId() != null) {
                $batch_wise_cost = $this->getBatchWiseCost($each_product, self::QUANTITY_INCREASED);
                foreach ($batch_wise_cost as $batch) {
                    $data [] = [
                        'id' => $sku_details[$sku_id]['product_id'],
                        'sku_id' => $sku_details[$sku_id]['id'],
                        'name' => $sku_details[$sku_id]['name'] ?? '',
                        "unit_price" => (double)$batch['unit_price'],
                        "selling_price" => $each_product->isQuantityIncreased() ? $each_product->getCurrentUnitPrice() : $each_product->getOldUnitPrice(),
                        "quantity" => $each_product->getQuantityChangedValue(),
                        "type" => $each_product->isQuantityIncreased() ? OrderChangingTypes::QUANTITY_INCREASE : OrderChangingTypes::REFUND
                    ];
                }
            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => 0,
                    "selling_price" => (double)$order_sku->unit_price,
                    "quantity" => $each_product->getQuantityChangedValue(),
                    "type" => $each_product->isQuantityIncreased() ? OrderChangingTypes::QUANTITY_INCREASE : OrderChangingTypes::REFUND
                ];
            }
        }
        return $data;
    }

}
