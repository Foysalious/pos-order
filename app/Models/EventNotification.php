<?php

namespace App\Models;

class EventNotification extends BaseModel
{
    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

