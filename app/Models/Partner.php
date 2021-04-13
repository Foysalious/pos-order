<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends BaseModel
{
    use HasFactory;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
