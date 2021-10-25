<?php namespace App\Services\Order\Refund\Objects;


class ProductChangeTracker
{
    protected int $orderSkuId;
    protected float $quantity;
    protected float $currentQuantity;
    protected float $previousQuantity;
    protected float $oldUnitPrice;
    protected ?float $currentUnitPrice = null;
    protected bool $priceChanged = false;
    protected ?int $skuId;
    protected string $name;
    protected int $productId;
    protected float $previousVatPercentage;
    protected float $currentVatPercentage;
    protected array $previousDiscountDetails;
    protected array $currentDiscountDetails;

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
     * @param float|null $currentUnitPrice
     * @return $this
     */
    public function setCurrentUnitPrice(?float $currentUnitPrice)
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

    /**
     * @return bool
     */
    public function isCurrentUnitPriceSet() : bool
    {
        return !is_null($this->currentUnitPrice);
    }

    public function isVatPercentageChanged() : bool
    {
        return $this->currentVatPercentage != $this->previousVatPercentage;
    }

    public function isDiscountChanged()
    {
        return collect($this->currentDiscountDetails)->diffAssoc(collect($this->previousDiscountDetails))->count() > 0;
    }

    /**
     * @param float $vat_percentage
     * @return ProductChangeTracker
     */
    public function setPreviuosVatPercentage(float $vat_percentage)
    {
        $this->previousVatPercentage = $vat_percentage;
        return $this;
    }

    /**
     * @param float $vat_percentage
     * @return ProductChangeTracker
     */
    public function setCurrentVatPercentage(float $vat_percentage)
    {
        $this->currentVatPercentage = $vat_percentage;
        return $this;
    }

    /**
     * @param array $currentDiscountDetails
     * @return ProductChangeTracker
     */
    public function setCurrentDiscountDetails(array $currentDiscountDetails)
    {
        $this->currentDiscountDetails = [
            'discount' => (float) $currentDiscountDetails['discount'] ?? 0,
            'is_percentage' => $currentDiscountDetails['is_percentage'] ?? 0
        ];
        return $this;
    }

    /**
     * @param array|object|null $previousDiscountDetails
     * @return ProductChangeTracker
     */
    public function setPreviousDiscountDetails(array|object|null $previousDiscountDetails)
    {
        $this->previousDiscountDetails = [
            'discount' =>  $previousDiscountDetails ? (float) $previousDiscountDetails->original_amount : 0,
            'is_percentage' => $previousDiscountDetails ? $previousDiscountDetails->is_percentage : 0
        ];
        return $this;
    }

}
