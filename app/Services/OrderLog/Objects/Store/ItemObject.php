<?php namespace App\Services\OrderLog\Objects\Store;


use App\Models\OrderSku;
use JsonSerializable;

class ItemObject implements JsonSerializable
{
    private OrderSku $orderSku;

    /**
     * @param OrderSku $orderSku
     * @return ItemObject
     */
    public function setOrderSku(OrderSku $orderSku): ItemObject
    {
        $this->orderSku = $orderSku;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->orderSku->id,
            'order_id' => $this->orderSku->order_id,
            'name' => $this->orderSku->name,
            'sku_id' => $this->orderSku->sku_id,
            'details' => $this->orderSku->details,
            'quantity' => $this->orderSku->quantity,
            'unit_weight' => $this->orderSku->unit_weight,
            'unit_price' => $this->orderSku->unit_price,
            'batch_detail' => $this->orderSku->batch_detail,
            'is_emi_available' => $this->orderSku->is_emi_available,
            'unit' => $this->orderSku->unit,
            'vat_percentage' => $this->orderSku->vat_percentage,
            'warranty' => $this->orderSku->warranty,
            'warranty_unit' => $this->orderSku->warranty_unit,
            'note' => $this->orderSku->note,
            'product_image' => $this->orderSku->product_image,
            'created_by_name' => $this->orderSku->created_by_name,
            'updated_by_name' => $this->orderSku->updated_by_name,
            'created_at' => $this->orderSku->created_at,
            'updated_at' => $this->orderSku->updated_at,
            'deleted_at' => $this->orderSku->deleted_at,
        ];
    }
}
