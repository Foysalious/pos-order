<?php namespace App\Services\Accounting;


use App\Helper\Miscellaneous\RequestIdentification;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\Accounting\Constants\Accounts;
use App\Services\Accounting\Constants\Cash;
use App\Services\Accounting\Constants\Sales;
use App\Services\Order\Constants\SalesChannel;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\PriceCalculation;

class OrderDueEntry extends BaseEntry
{
    /**
     * @throws Exceptions\AccountingEntryServerError
     */
    public function create()
    {
        $data = $this->makeData();
        dd($data);
        $this->accountingRepository->updateEntryBySource($data, $this->order->id, $this->order->partner_id);
    }

    private function makeData(): array
    {
        $order_price_details = $this->getOrderPriceDetails(new PriceCalculation());
        $customer = $this->order->customer ?? null;

        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => Sales::SALES_FROM_POS,
            'debit_account_key'  => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id'          => $this->order->id,
            'source_type'        => EntryTypes::POS,
            'amount'             => $order_price_details->getDiscountedPrice(),
            'amount_cleared'     => $order_price_details->getPaid(),
            'total_discount'     => $order_price_details->getDiscount(),
            'total_vat'          => $order_price_details->getVat(),
            'delivery_charge'    => (double) $this->order->delivery_charge ??  0,
            'bank_transaction_charge' => (double) $this->order->bank_transaction_charge ??  0,
            'interest'           => (double) $this->order->interest ??  0,
            'note'               => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ?  SalesChannel::WEBSTORE : SalesChannel::POS,
            'entry_at'           => $this->order->updated_at->format('Y-m-d H:i:s'),
            'reconcile_amount'   => $this->paidAmount,
            'inventory_'    => '',
        ];

        return array_merge($data,$this->makeCustomerData($customer));
    }
}
