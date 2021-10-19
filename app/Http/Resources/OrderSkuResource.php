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
        $unit_discount = $this->discount->amount / $this->quantity;
        $unit_discounted_price_without_vat = $this->unit_price - $unit_discount;
        $vat = ($unit_discounted_price_without_vat * $this->vat_percentage) / 100;
        $this->resource->calculate();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku_id' => $this->sku_id,
            'app_thumb' => $this->product_image ?: $default_product_app_thumb,
            'combination' => $sku_details ?? null,
            'quantity' => $this->quantity,
            'unit_original_price' => $this->unit_price,
            'unit_discounted_price' => $unit_discounted_price_without_vat + $vat,
            'unit_discounted_price_without_vat' => $unit_discounted_price_without_vat,
            'unit_discount' => $unit_discount,
            'is_emi_available' => $this->is_emi_available,
            'unit' => $this->unit,
            'vat_percentage' => $this->vat_percentage,
            'warranty' => $this->warranty,
            'warranty_unit' => $this->warranty_unit,
            'note' => $this->note,
        ];
    }
}
