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
}
