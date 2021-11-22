<?php namespace App\Services\Order\Objects;


class OrderPriceObject
{
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
     * @param float|null $original_price
     * @return OrderPriceObject
     */
    public function setOriginalPrice(?float $original_price): OrderPriceObject
    {
        $this->original_price = $original_price;
        return $this;
    }

    /**
     * @param float|null $discounted_price_without_vat
     * @return OrderPriceObject
     */
    public function setDiscountedPriceWithoutVat(?float $discounted_price_without_vat): OrderPriceObject
    {
        $this->discounted_price_without_vat = $discounted_price_without_vat;
        return $this;
    }

    /**
     * @param float|null $promo_discount
     * @return OrderPriceObject
     */
    public function setPromoDiscount(?float $promo_discount): OrderPriceObject
    {
        $this->promo_discount = $promo_discount;
        return $this;
    }

    /**
     * @param float|null $order_discount
     * @return OrderPriceObject
     */
    public function setOrderDiscount(?float $order_discount): OrderPriceObject
    {
        $this->order_discount = $order_discount;
        return $this;
    }

    /**
     * @param float|null $vat
     * @return OrderPriceObject
     */
    public function setVat(?float $vat): OrderPriceObject
    {
        $this->vat = $vat;
        return $this;
    }

    /**
     * @param float|null $delivery_charge
     * @return OrderPriceObject
     */
    public function setDeliveryCharge(?float $delivery_charge): OrderPriceObject
    {
        $this->delivery_charge = $delivery_charge;
        return $this;
    }

    /**
     * @param float|null $discounted_price
     * @return OrderPriceObject
     */
    public function setDiscountedPrice(?float $discounted_price): OrderPriceObject
    {
        $this->discounted_price = $discounted_price;
        return $this;
    }

    /**
     * @param float|null $paid
     * @return OrderPriceObject
     */
    public function setPaid(?float $paid): OrderPriceObject
    {
        $this->paid = $paid;
        return $this;
    }

    /**
     * @param float|null $due
     * @return OrderPriceObject
     */
    public function setDue(?float $due): OrderPriceObject
    {
        $this->due = $due;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }
}
