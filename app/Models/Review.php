<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function images()
    {
        return $this->hasMany(ReviewImage::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id', 'id');
    }

    public function orderSku()
    {
        return $this->belongsTo(OrderSku::class,'order_sku_id');
    }

    public function variation()
    {
        return $this->orderSku && $this->orderSku->details ?  json_decode($this->orderSku->details, true)["combination"] : null;
    }
}
