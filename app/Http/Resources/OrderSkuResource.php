<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSkuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $sku_details = json_decode($this->details);
        $default_product_app_thumb = config('s3.url') . "images/pos/services/thumbs/default.jpg";
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku_id' => $this->sku_id,
            'app_thumb' => $this->product_image ?: $default_product_app_thumb,
            'combination' => $sku_details->combination ?? null,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'is_emi_available' => $this->is_emi_available,
            'unit' => $this->unit,
            'vat_percentage' => $this->vat_percentage,
            'warranty' => $this->warranty,
            'warranty_unit' => $this->warranty_unit,
            'note' => $this->note,
        ];
    }
}
