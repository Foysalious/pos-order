<?php namespace App\Models;

use App\Http\Resources\ProductRatingReview;
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
        return $this->hasOne(OrderDiscount::class, 'type_id','id');
    }

    public function getProductRatingReview($channel_id, $partner_id)
    {
        /** @var ProductRatingReview $productIdAndName */
        $productIdAndName = app(ProductRatingReview::class);
        return $productIdAndName->getProductRatingReview($this, $channel_id, $partner_id);
    }

}
