<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['id' => 'string'];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
