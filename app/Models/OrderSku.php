<?php namespace App\Models;

use App\Http\Resources\ProductIdAndName;
use App\Services\OrderSku\OrderSkuTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderSku extends BaseModel
{
    use HasFactory, OrderSkuTrait, SoftDeletes;

    protected $guarded = ['id'];
    protected $casts = ['unit_price' => 'double', 'quantity' => 'double', 'vat_percentage' => 'double'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'order_sku_id');
    }

    public function discount()
    {
        return $this->hasOne(OrderDiscount::class, 'item_id');
    }

    public function getProductIdAndName($channel_id, $partner_id)
    {
        /** @var ProductIdAndName $productIdAndName */
        $productIdAndName = app(ProductIdAndName::class);
        return $productIdAndName->getProductRatingReview($this, $channel_id, $partner_id);
    }

}
