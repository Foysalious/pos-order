<?php namespace App\Services\Order\Refund\Objects;


class ProductChangeTracker
{
    protected int $orderSkuId;
    protected float $quantity;
    protected float $quantityChangedValue;
    protected bool  $quantityIncreased;
    protected bool  $quantityDecreased;
    protected float $oldUnitPrice;
    protected float $currentUnitPrice;
    protected bool $priceChanged = false;
    protected ?int $skuId;
    protected bool $quantityChanged = false;
    protected string $name;
    protected int $productId;

    /**
     * @param int $orderSkuId
     * @return $this
     */
    public function setOrderSkuId(int $orderSkuId)
    {
        $this->orderSkuId = $orderSkuId;
        return $this;
    }

    /**
     * @param float $quantity
     * @return $this
     */
    public function setQuantity(float $quantity)
    {
        $this->quantity = $quantity;
        $this->quantityChanged = true;
        return $this;
    }

    /**
     * @param float $quantityChangedValue
     * @return $this
     */
    public function setQuantityChangedValue(float $quantityChangedValue)
    {
        $this->quantityChangedValue = $quantityChangedValue;
        return $this;
    }

    /**
     * @param bool $quantityIncreased
     * @return $this
     */
    public function setQuantityIncreased(bool $quantityIncreased)
    {
        $this->quantityIncreased = $quantityIncreased;
        $this->quantityDecreased = !$quantityIncreased;
        return $this;
    }

    /**
     * @param bool $quantityDecreased
     * @return $this
     */
    public function setQuantityDecreased(bool $quantityDecreased)
    {
        $this->quantityDecreased = $quantityDecreased;
        $this->quantityIncreased = !$quantityDecreased;
        return $this;
    }

    /**
     * @param float $oldUnitPrice
     * @return $this
     */
    public function setOldUnitPrice(float $oldUnitPrice)
    {
        $this->oldUnitPrice = $oldUnitPrice;
        return $this;
    }

    /**
     * @param int|null $skuId
     * @return $this
     */
    public function setSkuId(?int $skuId)
    {
        $this->skuId = $skuId;
        return $this;
    }

    /**
     * @param float $currentUnitPrice
     * @return $this
     */
    public function setCurrentUnitPrice(float $currentUnitPrice)
    {
        $this->currentUnitPrice = $currentUnitPrice;
        $this->priceChanged = !($this->currentUnitPrice == $this->oldUnitPrice);
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderSkuId(): int
    {
        return $this->orderSkuId;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getQuantityChangedValue(): float
    {
        return $this->quantityChangedValue;
    }

    /**
     * @return bool
     */
    public function isQuantityIncreased(): bool
    {
        return $this->quantityIncreased;
    }

    /**
     * @return bool
     */
    public function isQuantityDecreased(): bool
    {
        return $this->quantityDecreased;
    }

    /**
     * @return float
     */
    public function getOldUnitPrice(): float
    {
        return $this->oldUnitPrice;
    }

    /**
     * @return float
     */
    public function getCurrentUnitPrice(): float
    {
        return $this->currentUnitPrice;
    }

    /**
     * @return int|null
     */
    public function getSkuId(): ?int
    {
        return $this->skuId;
    }

    /**
     * @return bool
     */
    public function isPriceChanged(): bool
    {
        return $this->priceChanged;
    }

    /**
     * @return bool
     */
    public function isQuantityChanged(): bool
    {
        return $this->quantityChanged;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId(int $productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

}
