<?php namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'product_id'            => $this->product_id,
            'customer_id'           => $this->customer_id,
            'order_sku_id'          => $this->order_sku_id,
            'review_title'          => $this->review_title,
            'review_details'        => $this->review_details,
            'images'                => $this->images,
            'variation'             => $this->variation()??null,
            'rating'                => $this->rating,
            'created_at'            => convertTimezone($this->created_at)?->format('Y-m-d H:i:s'),
            'product_name'          => $this->orderSku->name,
            'product_image'         => $this->orderSku->product_image,
        ];
    }

}
