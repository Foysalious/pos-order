<?php namespace App\Services\Accounting;

use App\Helper\Miscellaneous\RequestIdentification;
use App\Models\Customer;
use App\Repositories\Accounting\AccountingRepository;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\Accounting\Constants\Accounts;
use App\Services\Accounting\Constants\Cash;
use App\Services\Accounting\Constants\Sales;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannel;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\PriceCalculation;
use App\Services\OrderSku\BatchManipulator;
use Illuminate\Support\Facades\App;

class CreateEntry extends BaseEntry
{

    public function __construct(AccountingRepository $accountingRepository, InventoryServerClient $client)
    {
        parent::__construct($accountingRepository, $client);
    }

    public function create()
    {
        $data = $this->makeData();
        $this->accountingRepository->storeEntry($this->order->partner_id, $data);
    }

    public function makeData(): array
    {
        $order_price_details = $this->getOrderPriceDetails(new PriceCalculation());
        $customer = Customer::where('id', $this->order->customer_id)->where('partner_id', $this->order->partner_id)->first();
        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Sales::SALES_FROM_ECOM : Sales::SALES_FROM_POS,
            'debit_account_key' => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id' => $this->order->id,
            'note' => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? SalesChannel::WEBSTORE : SalesChannel::POS,
            'source_type' => EntryTypes::POS,
            'amount' => $order_price_details->getDiscountedPrice(),
            'amount_cleared' => $order_price_details->getPaid(),
            'total_discount' => $order_price_details->getDiscount(),
            'total_vat' => $order_price_details->getVat(),
            'entry_at' => convertTimezone($this->order->created_at)?->format('Y-m-d H:i:s'),
            'delivery_charge' => (double)$this->order->delivery_charge ?? 0,
            'bank_transaction_charge' => (double)$this->order->bank_transaction_charge ?? 0,
            'interest' => (double)$this->order->interest ?? 0,
            'inventory_products' => $this->getOrderedItemsData(),
        ];
        return array_merge($data,$this->makeCustomerData($customer));
    }

    private function getOrderedItemsData(): bool|string|null
    {
        $data = [];
        $ordered_skus = $this->order->orderSkus()->get();
        $skus_ids = $ordered_skus->where('sku_id', '<>', null)->pluck('sku_id')->toArray();
        if ($skus_ids) {
            $sku_details = collect($this->getSkuDetails($skus_ids, $this->order->sales_channel_id))->keyBy('id')->toArray();
        }
        /** @var BatchManipulator $mapper */
        $mapper = App::make(BatchManipulator::class);
        foreach ($ordered_skus as $sku) {
            if (!is_null($sku->sku_id)) {
                $batches = $mapper->setBatchDetail($sku->batch_detail)->getBatchDetails();
                foreach ($batches as $batch) {
                    $data [] = [
                        'id' => $sku_details[$sku->sku_id]['product_id'],
                        'sku_id' => $sku->sku_id,
                        'name' => $sku->name,
                        'unit_price' => (double) $batch['cost'] ?? $sku->unit_price,
                        'selling_price' => (double)$sku->unit_price ?? $sku->unit_price,
                        'quantity' => (double) $batch['quantity'] ?? $sku->quantity
                    ];
                }

            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    'unit_price' => (double)$sku->unit_price,
                    'selling_price' => (double)$sku->unit_price,
                    'quantity' => (double) $sku->quantity
                ];
            }
        }
        return $data ? json_encode($data) : null;
    }


}
