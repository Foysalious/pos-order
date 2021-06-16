<?php namespace App\Services\Accounting;

use App\Helper\Miscellaneous\RequestIdentification;
use App\Models\Order;
use App\Repositories\Accounting\AccountingRepository;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\Accounting\Constants\Accounts;
use App\Services\Accounting\Constants\Cash;
use App\Services\Accounting\Constants\Sales;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannel;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\PriceCalculation;
use App\Traits\ModificationFields;
use Illuminate\Support\Facades\App;

class Creator
{
    use ModificationFields;
    protected AccountingRepository $accountingRepository;
    protected Order $order;
    /** @var InventoryServerClient $client */
    private InventoryServerClient $client;

    /**
     * Creator constructor.
     * @param AccountingRepository $accountingRepository
     * @param Order $order
     * @param InventoryServerClient $client
     */
    public function __construct(AccountingRepository $accountingRepository, Order $order, InventoryServerClient $client)
    {
        $this->accountingRepository = $accountingRepository;
        $this->order = $order;
        $this->client = $client;
    }


    public function create()
    {
        $data = $this->makeData();
        $this->accountingRepository->storeEntry($this->order->partner_id, $data);
        dd('died in creator');
    }

    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    private function makeData()
    {
        /** @var PriceCalculation $order_price_details */
        $order_price_details = (App::make(PriceCalculation::class))->setOrder($this->order);

        $customer = $this->order->customer->only('id','name');
        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => Sales::SALES_FROM_POS,
            'debit_account_key'  => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id'          => $this->order->id,
            'note'               => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ?  SalesChannel::WEBSTORE : SalesChannel::POS,
            'source_type'        => EntryTypes::POS,
            'amount'             => (double)$order_price_details->getTotalBill(),
            'amount_cleared'     => (double) $order_price_details->getTotalBill(),
            'total_discount'     => (double) $order_price_details->getDiscountAmount(),
            'total_vat'          => (double) $order_price_details->getTotalVat(),
            'entry_at' => $this->order->created_at->format('Y-m-d H:i:s'),
            'inventory_products' => $this->getOrderedItemsData(),
            'customer_id' => $customer['id'],
            'customer_name' => $customer['name'],
        ];
        return $data;

    }

    private function getOrderedItemsData()
    {
        $data = [];
        $ordered_skus = $this->order->orderSkus()->get();
        $skus_ids = $ordered_skus->where('sku_id', '<>', null)->pluck('sku_id')->toArray();
        $sku_details = collect($this->getSkuDetails($skus_ids, $this->order->sales_channel_id))->keyBy('id')->toArray();

        foreach ($ordered_skus as $sku) {
            if (isset($sku_details[$sku->sku_id])) {
                $data [] = [
                    'id' => $sku->sku_id,
                    'name' => $sku->name,
                    'unit_price' => (double)$sku_details[$sku->sku_id]['sku_channel'][0]['price'],
                    'selling_price' => (double)$sku->unit_price,
                    'quantity' => $sku->quantity
                ];
            } else {
               $data [] = [
                   'id' => 0,
                   'name' => 'Custom Amount',
                   'unit_price' => (double)$sku->unit_price,
                   'selling_price' => (double)$sku->unit_price,
                   'quantity' => $sku->quantity
               ];
            }
        }

        return $data ? json_encode($data) : null;
    }

    private function getSkuDetails($sku_ids, $sales_channel_id)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&channel_id='.$sales_channel_id;
        $response = $this->client->get($url);
        return $response['skus'];
    }
}
