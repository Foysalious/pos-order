<?php namespace App\Http\Resources\Webstore\Customer;

use App\Models\OrderSku;
use Illuminate\Http\Resources\Json\JsonResource;


class NotRatedSkuResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var OrderSku $this */
        list($product_name, $product_id) = $this->getProductRatingReview($this->order->sales_channel_id, $this->order->partner_id);
        $details = json_decode($this->details, true);

        return [
            'sku_id' => $this->id,
            'product_id' => $product_id ?? null,
            'product_name' => $product_name ?? null,
            'product_image' => $this->product_image ?? null,
            'order_id' => $this->order_id,
            'variation' => $details['combination'] ?? null,
        ];
    }
}
