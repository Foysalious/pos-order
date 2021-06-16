<?php namespace App\Services\OrderSku;

use function App\Helper\Formatters\formatTakaToDecimal;

trait OrderSkuTrait
{
    private float $price;
    private float $unit_price;
    private float $quantity;
    private float $discountAmount;
    private float $priceAfterDiscount;
    private float $priceWithVat;
    private float $total;
    private float $vat;
    private bool $isCalculated;

    public function calculate()
    {
        $this->price = ($this['unit_price'] * $this['quantity']);
        $this->discountAmount = $this->discount ? (($this->price > $this->discount->amount) ? $this->discount->amount : $this->price) : 0.00;
        $this->priceAfterDiscount = $this->price - $this->discountAmount;
        $this->vat = ($this->priceAfterDiscount * $this->vat_percentage) / 100;
        $this->priceWithVat = $this->price + $this->vat;
        $this->total = $this->priceWithVat - $this->discountAmount;
        $this->isCalculated = true;
        $this->formatAllToTaka();

        return $this;
    }

    private function formatAllToTaka()
    {
        $this->price = formatTakaToDecimal($this->price);
        $this->vat = formatTakaToDecimal($this->vat);
        $this->priceWithVat = formatTakaToDecimal($this->priceWithVat);
        $this->discountAmount = formatTakaToDecimal($this->discountAmount);
        $this->total = formatTakaToDecimal($this->total);
    }
    public function getPrice()
    {
        return $this->price;
    }

    public function getVat()
    {
        return $this->vat;
    }

    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    public function getTotal()
    {
        return $this->total;
    }
}
