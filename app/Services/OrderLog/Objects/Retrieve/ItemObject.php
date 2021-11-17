<?php namespace App\Services\OrderLog\Objects\Retrieve;


class ItemObject
{
    private $id;
    private $order_id;
    private $name;
    private $sku_id;
    private $details;
    private $quantity;
    private $unit_weight;
    private $unit_price;
    private $batch_detail;
    private $is_emi_available;
    private $unit;
    private $vat_percentage;
    private $warranty;
    private $warranty_unit;
    private $note;
    private $product_image;
    private $created_by_name;
    private $updated_by_name;
    private $created_at;
    private $updated_at;
    private $deleted_at;

    protected $originalPrice;
    protected $discountAmount;
    protected $discountedPriceWithoutVat;
    protected $priceWithVat;
    protected $discountedPrice;
    protected $vat;

    /**
     * @param mixed $id
     * @return ItemObject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $order_id
     * @return ItemObject
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param mixed $name
     * @return ItemObject
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $sku_id
     * @return ItemObject
     */
    public function setSkuId($sku_id)
    {
        $this->sku_id = $sku_id;
        return $this;
    }

    /**
     * @param mixed $details
     * @return ItemObject
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param mixed $quantity
     * @return ItemObject
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @param mixed $unit_weight
     * @return ItemObject
     */
    public function setUnitWeight($unit_weight)
    {
        $this->unit_weight = $unit_weight;
        return $this;
    }

    /**
     * @param mixed $unit_price
     * @return ItemObject
     */
    public function setUnitPrice($unit_price)
    {
        $this->unit_price = $unit_price;
        return $this;
    }

    /**
     * @param mixed $batch_detail
     * @return ItemObject
     */
    public function setBatchDetail($batch_detail)
    {
        $this->batch_detail = $batch_detail;
        return $this;
    }

    /**
     * @param mixed $is_emi_available
     * @return ItemObject
     */
    public function setIsEmiAvailable($is_emi_available)
    {
        $this->is_emi_available = $is_emi_available;
        return $this;
    }

    /**
     * @param mixed $unit
     * @return ItemObject
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @param mixed $vat_percentage
     * @return ItemObject
     */
    public function setVatPercentage($vat_percentage)
    {
        $this->vat_percentage = $vat_percentage;
        return $this;
    }

    /**
     * @param mixed $warranty
     * @return ItemObject
     */
    public function setWarranty($warranty)
    {
        $this->warranty = $warranty;
        return $this;
    }

    /**
     * @param mixed $warranty_unit
     * @return ItemObject
     */
    public function setWarrantyUnit($warranty_unit)
    {
        $this->warranty_unit = $warranty_unit;
        return $this;
    }

    /**
     * @param mixed $note
     * @return ItemObject
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param mixed $product_image
     * @return ItemObject
     */
    public function setProductImage($product_image)
    {
        $this->product_image = $product_image;
        return $this;
    }

    /**
     * @param mixed $created_by_name
     * @return ItemObject
     */
    public function setCreatedByName($created_by_name)
    {
        $this->created_by_name = $created_by_name;
        return $this;
    }

    /**
     * @param mixed $updated_by_name
     * @return ItemObject
     */
    public function setUpdatedByName($updated_by_name)
    {
        $this->updated_by_name = $updated_by_name;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return ItemObject
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @param mixed $updated_at
     * @return ItemObject
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @param mixed $deleted_at
     * @return ItemObject
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }

    public function calculate()
    {
        $this->originalPrice = ($this->unit_price * $this->quantity);
        $this->discountAmount = isset($this->discount) ? (($this->originalPrice > $this->discount->amount) ? $this->discount->amount : $this->originalPrice) : 0.00;
        $this->discountedPriceWithoutVat = $this->originalPrice - $this->discountAmount;
        $this->vat = ($this->discountedPriceWithoutVat * $this->vat_percentage) / 100;
        $this->discountedPrice = $this->discountedPriceWithoutVat + $this->vat;
        $this->formatAllToTaka();
        return $this;
    }

    protected function formatAllToTaka()
    {
        $this->originalPrice = formatTakaToDecimal($this->originalPrice);
        $this->discountAmount = formatTakaToDecimal($this->discountAmount);
        $this->discountedPriceWithoutVat = formatTakaToDecimal($this->discountedPriceWithoutVat);
        $this->vat = formatTakaToDecimal($this->vat);
        $this->discountedPrice = formatTakaToDecimal($this->discountedPrice);
    }

    /**
     * Original price of a product/sku (without VAT and discount)
     * @return float
     */
    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }

    /**
     * Discount Amount of a product/sku
     * @return float
     */
    public function getDiscountAmount(): float
    {
        return $this->discountAmount;
    }

    /**
     * VAT of a product/sku
     * @return float
     */
    public function getVat(): float
    {
        return $this->vat;
    }

    /**
     * Discounted price of a product/sku without VAT
     * @return float
     */
    public function discountedPriceWithoutVat(): float
    {
        return $this->discountedPriceWithoutVat;
    }

    /**
     * Discounted price of a product/sku with VAT
     * Customer payable price
     * @return float
     */
    public function getDiscountedPrice(): float
    {
        return $this->discountedPrice;
    }


}
