<?php namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderPayment extends BaseModel
{
    use HasFactory;

    protected $casts = ['amount' => 'double'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeDebit($query)
    {
        return $query->type('Debit');
    }

    public function scopeCredit($query)
    {
        return $query->type('Credit');
    }

}
