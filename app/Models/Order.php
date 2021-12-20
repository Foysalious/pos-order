<?php namespace App\Models;

use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Order\Constants\OrderLogTypes;
use App\Services\Order\Constants\SalesChannel;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\PaymentLink\Constants\TargetType;
use App\Services\PaymentLink\Target;
use App\Services\Transaction\Constants\TransactionTypes;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends BaseModel
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $guarded = ['id'];
    protected $cascadeDeletes = ['orderSkus', 'discounts', 'logs', 'payments'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
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

    public function apiRequest()
    {
        return $this->belongsTo(ApiRequest::class);
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

    public function statusChangeLogs(): HasMany
    {
        return $this->logs()->where('type', OrderLogTypes::ORDER_STATUS);
    }

    public function paymentMethod()
    {
        $lastPayment = $this->payments()->where('transaction_type', TransactionTypes::CREDIT)->orderBy('id', 'desc')->select('id', 'method')->first();
        return $lastPayment ? $lastPayment->method : 'cod';
    }

    public function isUpdated(): bool
    {
        $type = $this->logs->where('type', 'products_and_prices')->first();
        return !empty($type);
    }

    public function getPaymentLinkTarget(): Target
    {
        return new Target(TargetType::POS_ORDER, $this->id);
    }

    public function getWeight()
    {
        $totalWeight = 0;
        ($this->orderSkus)->each(function ($sku) use (&$totalWeight) {
            $totalWeight += (double)$sku->unit_weight * $sku->quantity;
        });
        return $totalWeight;
    }

    public function isWebStore()
    {
        return $this->sales_channel_id = SalesChannelIds::WEBSTORE;
    }

    public function getDeliveryVendor()
    {
        if (!$this->delivery_vendor) return null;
        return json_decode($this->delivery_vendor)->name;
    }
}
