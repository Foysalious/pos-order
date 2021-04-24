<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends BaseModel
{
    protected $guarded = ['id'];
    use HasFactory;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
