<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderLog extends BaseModel
{
    use HasFactory;
    protected $guarded = ['id'];
    public $timestamps = false;
}
