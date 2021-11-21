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

    /**
     * @param mixed $id
     * @return OrderSkuObject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $name
     * @return OrderSkuObject
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $product_id
     * @return OrderSkuObject
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
        return $this;
    }

    /**
     * @param mixed $product_name
     * @return OrderSkuObject
     */
    public function setProductName($product_name)
    {
        $this->product_name = $product_name;
        return $this;
    }

    /**
     * @param mixed $note
     * @return OrderSkuObject
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param mixed $unit
     * @return OrderSkuObject
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @param mixed $sku_id
     * @return OrderSkuObject
     */
    public function setSkuId($sku_id)
    {
        $this->sku_id = $sku_id;
        return $this;
    }

    /**
     * @param mixed $original_price
     * @return OrderSkuObject
     */
    public function setOriginalPrice($original_price)
    {
        $this->original_price = $original_price;
        return $this;
    }

    /**
     * @param mixed $quantity
     * @return OrderSkuObject
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @param mixed $batch_detail
     * @return OrderSkuObject
     */
    public function setBatchDetail($batch_detail)
    {
        $this->batch_detail = $batch_detail;
        return $this;
    }

    /**
     * @param mixed $discount
     * @return OrderSkuObject
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param mixed $is_discount_percentage
     * @return OrderSkuObject
     */
    public function setIsDiscountPercentage($is_discount_percentage)
    {
        $this->is_discount_percentage = $is_discount_percentage;
        return $this;
    }

    /**
     * @param mixed $cap
     * @return OrderSkuObject
     */
    public function setCap($cap)
    {
        $this->cap = $cap;
        return $this;
    }

    /**
     * @param mixed $vat_percentage
     * @return OrderSkuObject
     */
    public function setVatPercentage($vat_percentage)
    {
        $this->vat_percentage = $vat_percentage;
        return $this;
    }

    /**
     * @param mixed $warranty
     * @return OrderSkuObject
     */
    public function setWarranty($warranty)
    {
        $this->warranty = $warranty;
        return $this;
    }

    /**
     * @param mixed $warranty_unit
     * @return OrderSkuObject
     */
    public function setWarrantyUnit($warranty_unit)
    {
        $this->warranty_unit = $warranty_unit;
        return $this;
    }

    /**
     * @param mixed $sku_channel_id
     * @return OrderSkuObject
     */
    public function setSkuChannelId($sku_channel_id)
    {
        $this->sku_channel_id = $sku_channel_id;
        return $this;
    }

    /**
     * @param mixed $channel_id
     * @return OrderSkuObject
     */
    public function setChannelId($channel_id)
    {
        $this->channel_id = $channel_id;
        return $this;
    }

    /**
     * @param mixed $channel_name
     * @return OrderSkuObject
     */
    public function setChannelName($channel_name)
    {
        $this->channel_name = $channel_name;
        return $this;
    }

    /**
     * @param mixed $combination
     * @return OrderSkuObject
     */
    public function setCombination($combination)
    {
        $this->combination = $combination;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }

}
