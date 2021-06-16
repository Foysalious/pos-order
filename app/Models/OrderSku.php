<?php namespace App\Models;

use App\Services\OrderSku\OrderSkuTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderSku extends BaseModel
{
    use HasFactory, OrderSkuTrait;
    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function discount()
    {
        return $this->hasOne(OrderDiscount::class, 'item_id');
    }
}
