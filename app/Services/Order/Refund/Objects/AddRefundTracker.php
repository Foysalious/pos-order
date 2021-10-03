<?php namespace App\Services\Order\Refund\Objects;


class AddRefundTracker
{
    protected int $orderSkuId;
    protected ?int $skuId;
    protected float $quantity;
    protected float $currentQuantity;
    protected float $previousQuantity;
    protected float $oldUnitPrice;
    protected float $currentUnitPrice;
    protected ?array $oldBatchDetail;
    protected ?array $updatedBatchDetail;

    /**
     * @param int $orderSkuId
     * @return $this
     */
    public function setOrderSkuId(int $orderSkuId) : AddRefundTracker
    {
        $this->orderSkuId = $orderSkuId;
        return $this;
    }

    /**
     * @param int|null $skuId
     * @return AddRefundTracker
     */
    public function setSkuId(?int $skuId) : AddRefundTracker
    {
        $this->skuId = $skuId;
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
     * @param string|null $oldBatchDetail
     * @return $this
     */
    public function setOldBatchDetail(?string $oldBatchDetail) : AddRefundTracker
    {
        $this->oldBatchDetail =json_decode($oldBatchDetail,true);
        return $this;
    }

    /**
     * @param string|null $updatedBatchDetail
     * @return $this
     */
    public function setUpdatedBatchDetail(?string $updatedBatchDetail) : AddRefundTracker
    {
        $this->updatedBatchDetail = json_decode($updatedBatchDetail,true);
        return $this;
    }

    /**
     * @param float $currentUnitPrice
     * @return $this
     */
    public function setCurrentUnitPrice(float $currentUnitPrice) : AddRefundTracker
    {
        $this->currentUnitPrice = $currentUnitPrice;
        return $this;
    }



    /**
     * @return bool
     */
    public function isQuantityDecreased(): bool
    {
        return $this->currentQuantity < $this->previousQuantity;
    }

    /**
     * @return bool
     */
    public function isQuantityIncreased(): bool
    {
        return $this->currentQuantity > $this->previousQuantity;
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
    public function getQuantityChangedValue(): float
    {
        return abs($this->currentQuantity-$this->previousQuantity);
    }

    /**
     * @return int|null
     */
    public function getSkuId(): ?int
    {
        return $this->skuId;
    }

    /**
     * @return int
     */
    public function getOrderSkuId(): int
    {
        return $this->orderSkuId;
    }

    /**
     * @return array|null
     */
    public function getUpdatedBatchDetail(): ?array
    {
        return $this->updatedBatchDetail;
    }

    /**
     * @return array|null
     */
    public function getOldBatchDetail(): ?array
    {
        return $this->oldBatchDetail;
    }

    /**
     * @param float $currentQuantity
     * @return $this
     */
    public function setCurrentQuantity(float $currentQuantity) : AddRefundTracker
    {
        $this->currentQuantity = $currentQuantity;
        return $this;
    }

    /**
     * @param float $previousQuantity
     * @return $this
     */
    public function setPreviousQuantity(float $previousQuantity) : AddRefundTracker
    {
        $this->previousQuantity = $previousQuantity;
        return $this;
    }

    /**
     * @return float
     */
    public function getQuantityDecreasedValue(): float
    {
        return $this->previousQuantity - $this->currentQuantity;
    }

    /**
     * @return float
     */
    public function getCurrentUnitPrice(): float
    {
        return $this->currentUnitPrice;
    }
}
