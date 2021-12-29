<?php namespace App\Services\OrderSku;


trait OrderSkuTrait
{
    protected float $originalPrice;
    protected float $unit_price;
    protected float $quantity;
    protected float $discountAmount;
    protected float $discountedPriceWithoutVat;
    protected float $priceWithVat;
    protected float $discountedPrice;
    protected float $vat;

    public function calculate()
    {
        $this->originalPrice = ($this['unit_price'] * $this['quantity']);
        $this->discountAmount = $this->discount ? (($this->originalPrice > $this->discount->amount) ? $this->discount->amount : $this->originalPrice) : 0.00;
        $this->discountedPriceWithoutVat = $this->originalPrice - $this->discountAmount;
        $this->vat = ($this->discountedPriceWithoutVat * $this->vat_percentage) / 100;
        $this->discountedPrice = $this->discountedPriceWithoutVat + $this->vat;
//        $this->formatAllToTaka();
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
