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

use Illuminate\Support\Facades\App;

class UpdateEntry extends BaseEntry
{
    /**
     * UpdateEntry constructor.
     */
    public function __construct(AccountingRepository $accountingRepository, InventoryServerClient $client)
    {
        parent::__construct($accountingRepository, $client);
    }

    public function update()
    {
        dd('died in updater');
        $data = $this->makeData();
        $this->accountingRepository->storeEntry($this->order->partner_id, $data);
    }

    private function makeData()
    {
        /** @var PriceCalculation $order_price_details */
        $order_price_details = $this->getOrderPriceDetails();

        $customer = $this->order->customer->only('id','name');
        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => Sales::SALES_FROM_POS,
            'debit_account_key'  => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id'          => $this->order->id,
            'source_type'        => EntryTypes::POS,
            'note'               => 'refund_type_confusion', //here will be refund type
            'amount'             => 'confusion',
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

}
