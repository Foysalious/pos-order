<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderSku extends BaseModel
{
    use HasFactory;

    public function calculate()
    {
        return true;
    }
}
