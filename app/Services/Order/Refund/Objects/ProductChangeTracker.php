<?php namespace App\Services\Order\Refund\Objects;


class ProductChangeTracker
{
    protected int $orderSkuId;
    protected float $quantity;
    protected float $currentQuantity;
    protected float $previousQuantity;
    protected float $oldUnitPrice;
    protected float $currentUnitPrice;
    protected bool $priceChanged = false;
    protected ?int $skuId;
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
        return abs($this->currentQuantity-$this->previousQuantity);
    }

    /**
     * @return bool
     */
    public function isQuantityIncreased(): bool
    {
        return $this->currentQuantity > $this->previousQuantity;
    }

    /**
     * @return bool
     */
    public function isQuantityDecreased(): bool
    {
        return $this->currentQuantity < $this->previousQuantity;
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
        return $this->currentQuantity != $this->previousQuantity;
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

    /**
     * @param float $currentQuantity
     * @return ProductChangeTracker
     */
    public function setCurrentQuantity(float $currentQuantity)
    {
        $this->currentQuantity = $currentQuantity;
        return $this;
    }

    /**
     * @param float $previousQuantity
     * @return ProductChangeTracker
     */
    public function setPreviousQuantity(float $previousQuantity)
    {
        $this->previousQuantity = $previousQuantity;
        return $this;
    }

    /**
     * @return float
     */
    public function getQuantityIncreasedValue(): float
    {
        return $this->currentQuantity-$this->previousQuantity;
    }

    /**
     * @return float
     */
    public function getQuantityDecreasedValue(): float
    {
        return $this->previousQuantity-$this->currentQuantity;
    }

    /**
     * @return float
     */
    public function getCurrentQuantity(): float
    {
        return $this->currentQuantity;
    }

    /**
     * @return float
     */
    public function getPreviousQuantity(): float
    {
        return $this->previousQuantity;
    }

}
