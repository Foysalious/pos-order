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

use Illuminate\Support\Facades\App;

class UpdateEntry extends BaseEntry
{
    protected array $orderProductChangeData;
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
        $calculate_amount_change = $this->calculateAmountChange($inventory_products);
        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => Sales::SALES_FROM_POS,
            'debit_account_key'  => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id'          => $this->order->id,
            'source_type'        => EntryTypes::POS,
            'note'               => $this->getNote(),
            'amount'             => $this->calculateAmountChange($inventory_products),
            'amount_cleared'     => $order_price_details->getPaid(),
            'total_discount'     => $order_price_details->getDiscount(),
            'total_vat'          => $order_price_details->getVat(),
            'entry_at' => $this->order->created_at->format('Y-m-d H:i:s'),
            'inventory_products' => json_encode($inventory_products),
            'customer_id' => $customer['id'],
            'customer_name' => $customer['name'],
        ];
        return $data;

    }

    private function makeInventoryProducts()
    {
        $data = [];
        $sku_ids [] = array_column($this->orderProductChangeData['new'] ?? [], 'id');
        $sku_ids [] = array_column($this->orderProductChangeData['deleted']['refunded_products'] ?? [], 'sku_id');
        $sku_ids [] = array_column($this->orderProductChangeData['refund_exchanged']['added_products'] ?? [], 'sku_id');
        $sku_ids [] = array_column($this->orderProductChangeData['refund_exchanged']['refunded_products'] ?? [], 'sku_id');
        $sku_ids = collect($sku_ids)->flatten()->whereNotNull()->toArray();
        $sku_details = collect($this->getSkuDetails($sku_ids,$this->order->sales_channel_id))->keyBy('id');
        $order_skus = $this->order->orderSkus()->get();

        $data = array_merge_recursive($this->makeNewProductsData($order_skus,$sku_details), $data);
        $data = array_merge_recursive($this->makeDeletedProductsData($order_skus,$sku_details), $data);
        $data = array_merge_recursive($this->makeRefundExchangedProductsData($order_skus,$sku_details), $data);

        return $data;

    }

    private function makeNewProductsData($order_skus, $sku_details)
    {
        $data = [];
        foreach ($this->orderProductChangeData['new'] as $new_item) {
            if ($new_item['id'] != null) {
                $sku_id = $new_item['id'];
                $data [] = [
                    'id' => $sku_details[$sku_id]['id'],
                    'name' => $sku_details[$sku_id]['name'] ?? '',
                    "unit_price" => (double) $sku_details[$sku_id]['sku_channel'][0]['price'],
                    "selling_price" => (double) $order_skus->where('sku_id', 816)->sortBy('created_at',SORT_REGULAR, true)->pluck('unit_price')->first(),
                    "quantity" => (double) $new_item['quantity'],
                    "type" => 'new'
                ];
            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => (double) $new_item['unit_price'],
                    "selling_price" => (double) $new_item['unit_price'],
                    "quantity" => (double) $new_item['quantity'],
                    "type" => 'new'
                ];
            }
        }
        return $data;
    }

    private function makeDeletedProductsData($order_skus, $sku_details)
    {
        $data = [];
        foreach ($this->orderProductChangeData['deleted']['refunded_products'] as $deleted_item) {
            if ($deleted_item['sku_id'] != null) {
                $sku_id = $deleted_item['sku_id'];
                $data [] = [
                    'id' => $sku_details[$sku_id]['id'],
                    'name' => $sku_details[$sku_id]['name'] ?? '',
                    "unit_price" => (double) $sku_details[$sku_id]['sku_channel'][0]['price'],
                    "selling_price" => (double) $order_skus->where('sku_id', $sku_id)->sortBy('created_at',SORT_REGULAR, true)->pluck('unit_price')->first(),
                    "quantity" => (double) $deleted_item['quantity'],
                    "type" => OrderChangingTypes::REFUND
                ];
            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => (double) $deleted_item['unit_price'],
                    "selling_price" => (double) $deleted_item['unit_price'],
                    "quantity" => (double) $deleted_item['quantity'],
                    "type" => OrderChangingTypes::REFUND
                ];
            }
        }
        return $data;
    }

    private function makeRefundExchangedProductsData($order_skus, $sku_details)
    {
        $data = [];
        //qauntity increasing products
        foreach ($this->orderProductChangeData['refund_exchanged']['added_products'] as $item) {
            if ($item['sku_id'] != null) {
                $sku_id = $item['sku_id'];
                $data [] = [
                    'id' => $sku_details[$sku_id]['id'],
                    'name' => $sku_details[$sku_id]['name'] ?? '',
                    "unit_price" => (double) $sku_details[$sku_id]['sku_channel'][0]['price'],
                    "selling_price" => (double) $order_skus->where('id', $item['id'])->pluck('unit_price')->first(),
                    "quantity" => (double) $item['quantity_changing_info']['value'],
                    "type" => OrderChangingTypes::QUANTITY_INCREASE
                ];
            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => (double) $order_skus->where('id', $item['id'])->pluck('unit_price')->first(),
                    "selling_price" => (double) $order_skus->where('id', $item['id'])->pluck('unit_price')->first(),
                    "quantity" => (double) $item['quantity_changing_info']['value'],
                    "type" => OrderChangingTypes::QUANTITY_INCREASE
                ];
            }
        }

        //qauntity decreasing products
        foreach ($this->orderProductChangeData['refund_exchanged']['refunded_products'] as $item) {
            if ($item['sku_id'] != null) {
                $sku_id = $item['sku_id'];
                $data [] = [
                    'id' => $sku_details[$sku_id]['id'],
                    'name' => $sku_details[$sku_id]['name'] ?? 'Nigga',
                    "unit_price" => (double) $sku_details[$sku_id]['sku_channel'][0]['price'],
                    "selling_price" => (double) $order_skus->where('id', $item['id'])->pluck('unit_price')->first(),
                    "quantity" => (double) $item['quantity_changing_info']['value'],
                    "type" => OrderChangingTypes::REFUND
                ];
            } else {
                $data [] = [
                    'id' => 0,
                    'name' => 'Custom Amount',
                    "unit_price" => (double) $order_skus->where('id', $item['id'])->pluck('unit_price')->first(),
                    "selling_price" => (double) $order_skus->where('id', $item['id'])->pluck('unit_price')->first(),
                    "quantity" => (double) $item['quantity_changing_info']['value'],
                    "type" => OrderChangingTypes::REFUND
                ];
            }
        }
        return $data;
    }

    private function calculateAmountChange($data)
    {
        $amount = 0;
        foreach ($data as $each) {
            if($each['type'] == OrderChangingTypes::QUANTITY_INCREASE) $amount = $amount + ($each['quantity']*$each['selling_price']);
            if($each['type'] == 'new') $amount = $amount + ($each['quantity']*$each['selling_price']);
            if($each['type'] == OrderChangingTypes::REFUND) $amount = $amount - ($each['quantity']*$each['selling_price']);
        }
        return $amount;
    }

    private function getNote()
    {
        $note = '';
        if(count($this->orderProductChangeData['new']) > 0) $note .= 'added' ;
        if(count($this->orderProductChangeData['deleted']) > 0) $note .= '-'. OrderChangingTypes::REFUND;
        if(count($this->orderProductChangeData['refund_exchanged']['added_products']) > 0) $note .= '-' . OrderChangingTypes::QUANTITY_INCREASE;
        if(count($this->orderProductChangeData['refund_exchanged']['refunded_products']) > 0) $note .= '-' . OrderChangingTypes::REFUND;
        return $note;
    }

}
