<?php namespace App\Services\Accounting;

use App\Helper\Miscellaneous\RequestIdentification;
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

    private function makeData(): array
    {
        /** @var PriceCalculation $order_price_details */
        $order_price_details = $this->getOrderPriceDetails();

        $customer = $this->order->customer ?? null;
        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => Sales::SALES_FROM_POS,
            'debit_account_key'  => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id'          => $this->order->id,
            'note'               => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ?  SalesChannel::WEBSTORE : SalesChannel::POS,
            'source_type'        => EntryTypes::POS,
            'amount'             => $order_price_details->getDiscountedPrice(),
            'amount_cleared'     => $order_price_details->getPaid(),
            'total_discount'     => $order_price_details->getDiscount(),
            'total_vat'          => $order_price_details->getVat(),
            'entry_at' => convertTimezone($this->order->created_at)->format('Y-m-d H:i:s'),
            'inventory_products' => $this->getOrderedItemsData(),
        ];

        if(!is_null($customer)) {
           $data = array_merge($data,$this->makeCustomerData($customer));
        }
        return $data;
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
                $batches = $mapper->setOrderSkuDetails($sku->details)->getBatchDetails();
                foreach ($batches as $batch) {
                    $data [] = [
                        'id' => $sku_details[$sku->sku_id]['product_id'],
                        'sku_id' => $sku->sku_id,
                        'name' => $sku->name,
                        'unit_price' => (double) $batch['cost'],
                        'selling_price' => (double)$sku->unit_price,
                        'quantity' => (double) $batch['quantity']
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
