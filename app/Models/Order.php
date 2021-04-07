<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory;

    public function items()
    {
        return $this->hasMany(OrderSku::class);
    }
}
