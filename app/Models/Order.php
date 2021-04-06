<?php namespace App\Models;

use App\Services\EMI\Calculations;
use App\Services\Order\Constants\PaymentStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use function App\Helper\Formatters\formatTakaToDecimal;

class Order extends BaseModel
{
    protected $guarded = ['id'];
    use HasFactory;

    public function calculate()
    {
        $this->_calculateThisItems();
        $this->totalDiscount = $this->totalItemDiscount + $this->discountsAmountWithoutProduct();
        $this->appliedDiscount = ($this->discountsAmountWithoutProduct() > $this->totalBill) ? $this->totalBill : $this->discountsAmountWithoutProduct();
        $this->originalTotal = round($this->totalBill - $this->appliedDiscount, 2);
        if (isset($this->emi_month) && !$this->interest) {
            $data = Calculations::getMonthData($this->originalTotal, (int)$this->emi_month, false);
            $this->interest = $data['total_interest'];
            $this->bank_transaction_charge = $data['bank_transaction_fee'];
            $this->update(['interest' => $this->interest, 'bank_transaction_charge' => $this->bank_transaction_charge]);
        }
        $this->netBill = $this->originalTotal + round((double)$this->interest, 2) + (double)round($this->bank_transaction_charge, 2);
        $this->netBill += (double)round($this->delivery_charge, 2);
        $this->_calculatePaidAmount();
        $this->paid = round($this->paid ?: 0, 2);

        $this->due = ($this->netBill - $this->paid) > 0 ? ($this->netBill - $this->paid) : 0;
        $this->_setPaymentStatus();
        $this->isCalculated = true;
        $this->_formatAllToTaka();
        return $this;


    }
    private function _formatAllToTaka()
    {
        $this->totalPrice = formatTakaToDecimal($this->totalPrice);
        $this->totalVat = formatTakaToDecimal($this->totalVat);
        $this->totalItemDiscount = formatTakaToDecimal($this->totalItemDiscount);
        $this->totalBill = formatTakaToDecimal($this->totalBill);
        return $this;
    }
    private function _setPaymentStatus() {
        $this->paymentStatus = ($this->due) ? PaymentStatuses::DUE : PaymentStatuses::PAID;
        return $this;
    }

    private function _calculatePaidAmount()
    {
        /**
         * USING AS A QUERY, THAT INCREASING LOAD TIME ON LIST VIEW
         *
         * $credit = $this->creditPayments()->sum('amount');
         * $debit  = $this->debitPayments()->sum('amount');
         *
         */
        $credit = $this->creditPaymentsCollect()->sum('amount');
        $debit = $this->debitPaymentsCollect()->sum('amount');
        $this->paid = $credit - $debit;
    }


    private function creditPaymentsCollect()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->transaction_type === 'Credit';
        });
    }

    private function debitPaymentsCollect()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->transaction_type === 'Debit';
        });
    }


    private function discountsAmountWithoutProduct()
    {
        return $this->discountsWithoutProduct()->sum('amount');
    }

    public function discountsWithoutProduct()
    {
        return $this->discounts()->whereNull('item_id');
    }

    public function discounts()
    {
        return $this->hasMany(OrderDiscount::class);
    }

    public function orderSkus()
    {
        return $this->hasMany(OrderSku::class);
    }

    private function _calculateThisItems()
    {
        $this->_initializeTotalsToZero();
        foreach ($this->orderSkus as $order_sku) {
            /** @var OrderSku $order_sku */
            $order_sku = $order_sku->calculate();
        }
        return $this;
    }

    private function _initializeTotalsToZero()
    {
        $this->totalPrice = 0;
        $this->totalVat = 0;
        $this->totalItemDiscount = 0;
        $this->totalBill = 0;
    }


}
