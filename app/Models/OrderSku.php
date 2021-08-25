<?php namespace App\Models;

use App\Services\OrderSku\OrderSkuTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use function App\Helper\Formatters\formatTakaToDecimal;

class OrderSku extends BaseModel
{
    use HasFactory, OrderSkuTrait,SoftDeletes;
    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class,'order_sku_id');
    }

    public function discount()
    {
        return $this->hasOne(OrderDiscount::class, 'item_id');
    }

}
