<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends BaseModel
{
    protected $guarded = [];
    protected $primaryKey = 'id';
    public $incrementing = false;

    use HasFactory,SoftDeletes;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
