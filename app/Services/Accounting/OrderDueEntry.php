<?php namespace App\Services\Accounting;


use App\Helper\Miscellaneous\RequestIdentification;
use App\Models\Customer;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\Accounting\Constants\Accounts;
use App\Services\Accounting\Constants\Cash;
use App\Services\Accounting\Constants\Sales;
use App\Services\Order\Constants\SalesChannel;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\PriceCalculation;

class OrderDueEntry extends BaseEntry
{
    const REFUND = 'refund';
    protected float $paidAmount;

    /**
     * @param float $paidAmount
     * @return OrderDueEntry
     */
    public function setPaidAmount(float $paidAmount)
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }
    /**
     * @throws Exceptions\AccountingEntryServerError
     */
    public function create()
    {
        $data = $this->makeData();
        $this->accountingRepository->updateEntryBySource($data, $this->order->id, $this->order->partner_id);
    }

    private function makeData(): array
    {
        $order_price_details = $this->getOrderPriceDetails(new PriceCalculation());
        $customer = Customer::where('id', $this->order->customer_id)->where('partner_id', $this->order->partner_id)->first();

        $data = [
            'created_from' => json_encode($this->withBothModificationFields((new RequestIdentification())->get())),
            'credit_account_key' => Sales::SALES_FROM_POS,
            'debit_account_key' => $this->order->sales_channel_id == SalesChannelIds::WEBSTORE ? Accounts::SHEBA_ACCOUNT : Cash::CASH,
            'source_id' => $this->order->id,
            'source_type' => EntryTypes::POS,
            'amount' => $order_price_details->getDiscountedPrice(),
            'amount_cleared' => $order_price_details->getPaid(),
            'total_discount' => $order_price_details->getDiscount(),
            'total_vat' => $order_price_details->getVat(),
            'delivery_charge' => (double)$this->order->delivery_charge ?? 0,
            'bank_transaction_charge' => (double)$this->order->bank_transaction_charge ?? 0,
            'interest' => (double)$this->order->interest ?? 0,
            'note' => self::REFUND,
            'entry_at' => $this->order->updated_at->format('Y-m-d H:i:s'),
            'reconcile_amount' => $this->paidAmount,
//            'updated_entry' => 'to_be_decided',
            'inventory_products' => null,
        ];

        return array_merge($data,$this->makeCustomerData($customer));
    }
}
