<?php namespace App\Services\Order\Objects;


class OrderSkuObject
{
    protected $skuDetails;
    protected $id;
    protected $name;
    protected $product_id;
    protected $product_name;
    protected $note;
    protected $unit;
    protected $sku_id;
    protected $original_price;
    protected $quantity;
    protected $batch_detail;
    protected $discount;
    protected $is_discount_percentage;
    protected $cap;
    protected $vat_percentage;
    protected $warranty;
    protected $warranty_unit;
    protected $sku_channel_id;
    protected $channel_id;
    protected $channel_name;
    protected $combination;

    public function setSkuDetails($skuDetails)
    {
        $this->skuDetails = $skuDetails;
        return $this;
    }

    public function build()
    {
        $this->id = $this->skuDetails->id;
        $this->name = $this->skuDetails->name;
        $this->product_id = $this->skuDetails->product_id;
        $this->product_name = $this->skuDetails->product_name;
        $this->note = $this->skuDetails->note;
        $this->unit = $this->skuDetails->unit;
        $this->sku_id = $this->skuDetails->sku_id;
        $this->original_price = $this->skuDetails->original_price;
        $this->quantity = $this->skuDetails->quantity;
        $this->batch_detail = $this->skuDetails->batch_detail;
        $this->discount = $this->skuDetails->discount;
        $this->is_discount_percentage = $this->skuDetails->is_discount_percentage;
        $this->cap = $this->skuDetails->cap;
        $this->vat_percentage = $this->skuDetails->vat_percentage;
        $this->warranty = $this->skuDetails->warranty;
        $this->warranty_unit = $this->skuDetails->warranty_unit;
        $this->sku_channel_id = $this->skuDetails->sku_channel_id;
        $this->channel_id = $this->skuDetails->channel_id;
        $this->channel_name = $this->skuDetails->channel_name;
        $this->combination = $this->skuDetails->combination;
        return $this;

    }

}
