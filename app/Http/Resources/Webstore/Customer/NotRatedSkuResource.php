<?php namespace App\Http\Resources\Webstore\Customer;

use Illuminate\Http\Resources\Json\JsonResource;


class NotRatedSkuResource extends JsonResource
{
    public function toArray($request)
    {
        $details = json_decode($this->details,true);
        return [
            'sku_id'    =>  $this->id,
            'product_id' => $details ? ($details['product_id'] ?? null): null,
            'product_name' => $details ? ($details['product_name'] ?? null): null,
            'product_image'  =>'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/20210611_233930.jpg',
            'order_id' => $this->order_id,
            'variation' => $details ? ($details['combination'] ?? null): null,
        ];
    }
}
