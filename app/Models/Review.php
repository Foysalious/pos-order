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

    public function variation()
    {
        return json_decode('[{"combination":[{"option_id":765,"option_name":"size","option_value_id":1518,"option_value_name":"l","option_value_details":[{"code":"L","type":"size"}]},{"option_id":766,"option_name":"color","option_value_id":1519,"option_value_name":"green","option_value_details":[{"code":"#000111","type":"color"}]}],"channel_data":[{"sku_channel_id":219,"channel_id":2,"price":95,"cost":90,"wholesale_price":100}]}]',true);
    }
}
