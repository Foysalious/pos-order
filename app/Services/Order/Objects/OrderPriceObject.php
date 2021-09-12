<?php namespace App\Services\Order\Objects;


class OrderPriceObject
{
    protected $priceDetails;
    protected ?float $original_price;
    protected ?float $discounted_price_without_vat;
    protected ?float $promo_discount;
    protected ?float $order_discount;
    protected ?float $vat;
    protected ?float $delivery_charge;
    protected ?float $discounted_price;
    protected ?float $paid;
    protected ?float $due;

    /**
     * @param mixed $priceDetails
     * @return OrderPriceObject
     */
    public function setPriceDetails($priceDetails)
    {
        dd($this->priceDetails);
        $this->priceDetails = $priceDetails;
        return $this;
    }

    public function build()
    {
        $this->original_price = $this->priceDetails->original_price;
        $this->discounted_price_without_vat = $this->priceDetails->discounted_price_without_vat;
        $this->promo_discount = $this->priceDetails->promo_discount;
        $this->order_discount = $this->priceDetails->order_discount;
        $this->vat = $this->priceDetails->vat;
        $this->delivery_charge = $this->priceDetails->delivery_charge;
        $this->discounted_price = $this->priceDetails->discounted_price;
        $this->paid = $this->priceDetails->paid;
        $this->due = $this->priceDetails->due;
        return $this;
    }

}
