<?php namespace App\Services\Accounting;


use App\Models\Customer;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\Accounting\Constants\Cash;
use App\Services\ClientServer\Exceptions\BaseClientServerError;

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
     * @throws BaseClientServerError
     */
    public function create()
    {
        $data = $this->makeData();
        $this->getNotifier()->storeEntry($this->order->partner_id, $data);
    }

    private function makeData(): array
    {
        $customer = Customer::where('id', $this->order->customer_id)->where('partner_id', $this->order->partner_id)->first();
        $data = [
            'amount' => $this->paidAmount,
            'customer_id' => $this->order->customer_id,
            'customer_is_supplier' => $customer->is_supplier,
            'customer_mobile' => $customer->mobile,
            'customer_name' => $customer->name,
            'source_id' => $this->order->id,
            'source_type' => EntryTypes::DEPOSIT,
            'debit_account_key' => Cash::CASH,
            'credit_account_key' => $this->order->customer_id,
            'reference' => 'Deposit From POS',
            'entry_at' => convertTimezone($this->order->updated_at)?->format('Y-m-d H:i:s'),
        ];
        return array_merge($data,$this->makeCustomerData($customer));
    }
}
