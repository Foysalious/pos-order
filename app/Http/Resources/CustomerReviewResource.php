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
            'variation'             => $this->variation(),
            'rating'                => $this->rating,
            'created_at'            => convertTimezone($this->created_at),
            'product_name'          => 'Floral Embroydary',
            'product_image'         =>'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/20210611_233930.jpg'
        ];
    }

}
