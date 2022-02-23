<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends BaseModel
{
    protected $guarded = [];
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $fillable = [
        'id',
        'name',
        'sub_domain',
        'sms_invoice',
        'auto_printing',
        'printer_name',
        'printer_model',
        'delivery_charge',
        'qr_code_account_type',
        'qr_code_image',
        'deleted_at',
        'created_by_name',
        'updated_by_name',
        'created_at',
        'updated_at'
    ];

    use HasFactory,SoftDeletes;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isAutoSmsOn(): bool
    {
        return (bool)$this->sms_invoice;
    }
}
