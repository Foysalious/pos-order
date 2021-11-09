<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends BaseModel
{
    protected $guarded = [];
    protected $primaryKey = 'id';
    public $incrementing = false;

    use HasFactory;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
