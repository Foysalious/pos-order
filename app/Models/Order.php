<?php namespace App\Models;

use App\Events\RewardOnOrderCreate;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Transaction\Constants\TransactionTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory;
    public static  $savedEventClass = RewardOnOrderCreate::class;
    protected $guarded = ['id'];

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

    public function orderDiscounts()
    {
        return $this->discounts()->where('type', DiscountTypes::ORDER);
    }

    public function voucherDiscounts()
    {
        return $this->discounts()->where('type', DiscountTypes::VOUCHER);
    }

    public function discounts()
    {
        return $this->hasMany(OrderDiscount::class);
    }

    public function orderSkus()
    {
        return $this->hasMany(OrderSku::class, 'order_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class);
    }

    public function paymentMethod()
    {
        $lastPayment = $this->payments()->where('transaction_type', TransactionTypes::CREDIT)->orderBy('id', 'desc')->select('id', 'method')->first();
        return $lastPayment ? $lastPayment->method : 'cod';
    }

    public function isUpdated() : bool
    {
        $type = $this->logs->where('type', 'products_and_prices')->first();
        return !empty($type);
    }
}
