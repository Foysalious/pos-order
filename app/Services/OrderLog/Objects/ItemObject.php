<?php namespace App\Services\OrderLog\Objects;


use App\Models\OrderSku;
use JsonSerializable;

class ItemObject implements JsonSerializable
{
    private ?int $id;
    private ?int $order_id;
    private ?string $name;
    private $sku_id;
    private ?string $details;
    private ?float $quantity;
    private ?float $unit_weight;
    private ?float $unit_price;
    private ?string $batch_detail;
    private ?int $is_emi_available;
    private ?string $unit;
    private ?float $vat_percentage;
    private ?int $warranty;
    private ?string $warranty_unit;
    private ?string $note;
    private ?string $product_image;
    private ?string $created_by_name;
    private ?string $updated_by_name;
    private ?string $created_at;
    private ?string $updated_at;
    private ?string $deleted_at;
    private ?string $discount;

    protected $originalPrice;
    protected $discountAmount;
    protected $discountedPriceWithoutVat;
    protected $priceWithVat;
    protected $discountedPrice;
    protected $vat;

    private OrderSku $orderSku;


    /**
     * @param OrderSku $orderSku
     * @return $this
     */
    public function setOrderSku(OrderSku $orderSku): ItemObject
    {
        $this->orderSku = $orderSku;
        return $this;
    }

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

    /**
     * @param mixed $discount
     * @return ItemObject
     */
    public function setDiscount($discount)
    {
        if ($discount) {
            /** @var DiscountObject $discountObject */
            $discountObject = app(DiscountObject::class);
            $discountObject->setId($discount->id)->setOrderId($discount->order_id)->setType($discount->type)
                ->setAmount($discount->amount)->setOriginalAmount($discount->original_amount)
                ->setIsPercentage($discount->is_percentage)->setCap($discount->cap)->setDiscountDetails($discount->discount_details)
                ->setDiscountId($discount->discount_id)->setTypeId($discount->type_id)->setCreatedByName($discount->created_by_name)
                ->setUpdatedByName($discount->updated_by_name)->setCreatedAt($discount->created_at)->setUpdatedAt($discount->updated_at)
                ->setDeletedAt($discount->deleted_at);
            $this->discount = $discountObject;
        }
        else {
            $this->discount = $discount;
        }
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
            'discount' => $this->orderSku->discount ? $this->getDiscount() : null
        ];
    }

    private function getDiscount(): DiscountObject
    {
        /** @var DiscountObject $discountObject */
        $discountObject = app(DiscountObject::class);
        $discountObject->setOrderDiscount($this->orderSku->discount);
        return $discountObject;
    }


}
