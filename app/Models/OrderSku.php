<?php namespace App\Models;

use App\Services\OrderSku\OrderSkuTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use function App\Helper\Formatters\formatTakaToDecimal;

class OrderSku extends BaseModel
{
    use HasFactory, OrderSkuTrait;
    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class,'order_sku_id');
    }

    public function calculate()
    {
        $this->price = ($this['unit_price'] * $this['quantity']);
        $this->discountAmount = $this->discount ? (($this->price > $this->discount->amount) ? $this->discount->amount : $this->price) : 0.00;
        $this->priceAfterDiscount = $this->price - $this->discountAmount;
        $this->vat = ($this->priceAfterDiscount * $this->vat_percentage) / 100;
        $this->priceWithVat = $this->price + $this->vat;
        $this->total = $this->priceWithVat - $this->discountAmount;
        $this->isCalculated = true;
        $this->_formatAllToTaka();

        return $this;
    }

    private function _formatAllToTaka()
    {
        $this->price = formatTakaToDecimal($this->price);
        $this->vat = formatTakaToDecimal($this->vat);
        $this->priceWithVat = formatTakaToDecimal($this->priceWithVat);
        $this->discountAmount = formatTakaToDecimal($this->discountAmount);
        $this->total = formatTakaToDecimal($this->total);
    }
    public function getPrice()
    {
        return $this->price;
    }

    public function getVat()
    {
        return $this->vat;
    }

    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function discount()
    {
        return $this->hasOne(OrderDiscount::class, 'item_id');
    }

}
