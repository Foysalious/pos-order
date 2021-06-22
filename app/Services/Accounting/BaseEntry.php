<?php namespace App\Services\Accounting;

use App\Models\Order;
use App\Repositories\Accounting\AccountingRepository;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\PriceCalculation;
use App\Traits\ModificationFields;
use Illuminate\Support\Facades\App;

class BaseEntry
{
    use ModificationFields;
    protected AccountingRepository $accountingRepository;
    protected Order $order;
    /** @var InventoryServerClient $client */
    protected InventoryServerClient $client;

    /**
     * Creator constructor.
     * @param AccountingRepository $accountingRepository
     * @param InventoryServerClient $client
     */
    public function __construct(AccountingRepository $accountingRepository, InventoryServerClient $client)
    {
        $this->accountingRepository = $accountingRepository;
        $this->client = $client;
    }

    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    protected function getOrderedItemsData(): bool|string|null
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
                    'quantity' => (double) $sku->quantity
                ];
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

    protected function getSkuDetails($sku_ids, $sales_channel_id)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&channel_id='.$sales_channel_id;
        $response = $this->client->setBaseUrl()->get($url);
        return $response['skus'];
    }

    protected function getOrderPriceDetails()
    {
        return  (App::make(PriceCalculation::class))->setOrder($this->order);
    }

}
