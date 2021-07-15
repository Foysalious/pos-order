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

        $customer = $this->order->customer->only('id','name');
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
            'entry_at' => $this->order->created_at->format('Y-m-d H:i:s'),
            'inventory_products' => $this->getOrderedItemsData(),
            'customer_id' => $customer['id'],
            'customer_name' => $customer['name'],
        ];
        return $data;
    }


}
