<?php namespace App\Services\Accounting;

use App\Models\Customer;
use App\Models\Order;
use App\Repositories\Accounting\AccountingRepository;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\PriceCalculation;
use App\Traits\ModificationFields;

abstract class BaseEntry
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

    protected function getSkuDetails($sku_ids, $sales_channel_id)
    {
        $url = 'api/v1/partners/' . $this->order->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&channel_id='.$sales_channel_id;
        $response = $this->client->setBaseUrl()->get($url);
        return $response['skus'];
    }

    protected function getOrderPriceDetails(PriceCalculation $priceCalculation): PriceCalculation
    {
        return  $priceCalculation->setOrder($this->order);
    }

    protected function makeCustomerData(Customer|null $customer) : array
    {
        if(!is_null($customer)){
            return [
                'customer_id' => is_string($customer->id) ? 5 : $customer->id,
                'customer_name' => $customer->name,
                'customer_mobile' => $customer->mobile,
                'customer_pro_pic' => $customer->pro_pic,
                'customer_is_supplier' => $customer->is_supplier,
            ];
        } else {
            return [
                'customer_id' => null,
                'customer_name' => null,
                'customer_mobile' => null,
                'customer_pro_pic' => null,
                'customer_is_supplier' => null,
            ];
        }
    }
}
