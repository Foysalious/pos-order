<?php namespace App\Models;

use App\Services\Discount\Constants\DiscountTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\PaymentLink\Target;
use App\Services\PaymentLink\Constants\TargetType;

class Order extends BaseModel
{
    use HasFactory;
    protected $guarded = ['id'];
    public $totalDiscount;
    public $totalItemDiscount;
    public $appliedDiscount;
    public $totalBill;
    public $originalTotal;
    public $interest;
    public $bank_transaction_charge;
    public $netBill;
    public $isCalculated;
    public $totalPrice;
    public $totalVat;
    public $paymentStatus;
    public $due;
    public $paid;


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(OrderSku::class);
    }
    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function calculate()
    {
        $this->_calculateThisItems();
        $this->totalDiscount = $this->totalItemDiscount + $this->discountsAmountWithoutProduct();
        $this->appliedDiscount = ($this->discountsAmountWithoutProduct() > $this->totalBill) ? $this->totalBill : $this->discountsAmountWithoutProduct();
        $this->originalTotal = round($this->totalBill - $this->appliedDiscount, 2);
        /*if (isset($this->emi_month) && !$this->interest) {
            $data = Calculations::getMonthData($this->originalTotal, (int)$this->emi_month, false);
            $this->interest = $data['total_interest'];
            $this->bank_transaction_charge = $data['bank_transaction_fee'];
            $this->update(['interest' => $this->interest, 'bank_transaction_charge' => $this->bank_transaction_charge]);
        }*/
        $this->netBill = $this->originalTotal + round((double)$this->interest, 2) + (double)round($this->bank_transaction_charge, 2);
        $this->netBill += round($this->delivery_charge, 2);
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
        $this->due = formatTakaToDecimal($this->due);
        return $this;
    }

    private function _setPaymentStatus()
    {
        $this->paymentStatus = ($this->due) ? "Due" : "Paid";
        return $this;
    }

    private function _calculatePaidAmount()
    {

        $credit = $this->creditPaymentsCollect()->sum('amount');
        $debit = $this->debitPaymentsCollect()->sum('amount');
        $this->paid = $credit - $debit;
    }

    private function creditPaymentsCollect()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->transaction_type === 'credit';
        });
    }

    private function debitPaymentsCollect()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->transaction_type === 'debit';
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

    public function getPaymentLinkTarget()
    {
        return new Target(TargetType::POS_ORDER, $this->id);
    }

    public function getVoucher()
    {
        return $this->discounts()->where('order_id', $this->id)
                                ->where('type', DiscountTypes::VOUCHER)
                                ->get();
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class);
    }

    public function isUpdated() : bool
    {
       $type = $this->logs->where('type', 'products_and_prices')->first();
       return !empty($type) ? true : false;
    }
}
