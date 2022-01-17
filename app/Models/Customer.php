<?php namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends BaseModel
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function details()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->mobile,
            'email' => $this->email,
            'image' => $this->pro_pic
        ];
    }
}
