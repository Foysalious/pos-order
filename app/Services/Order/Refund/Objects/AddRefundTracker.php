<?php


namespace App\Services\Order\Refund\Objects;


class AddRefundTracker
{
    protected int $orderSkuId;
    protected ?int $skuId;
    protected float $quantity;
    protected float $quantityChangedValue;
    protected bool  $quantityIncreased;
    protected bool  $quantityDecreased;
    protected float $oldUnitPrice;
    protected float $currentUnitPrice;
    protected ?array $oldBatchDetail;
    protected ?array $updatedBatchDetail;
    protected ?array $oldOrderSkuDetails;
    protected ?array $updatedOrderSkuDetails;

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
     * @param int|null $skuId
     */
    public function setSkuId(?int $skuId)
    {
        $this->skuId = $skuId;
        return $this;
    }

    /**
     * @param float $quantity
     * @return $this
     */
    public function setQuantity(float $quantity)
    {
        $this->quantity = $quantity;
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
     * @param float $oldUnitPrice
     * @return $this
     */
    public function setOldUnitPrice(float $oldUnitPrice)
    {
        $this->oldUnitPrice = $oldUnitPrice;
        return $this;
    }

    /**
     * @param string $oldSkuDetail
     * @return $this
     */
    public function setOldBatchDetail(string $oldSkuDetail)
    {
        $this->oldOrderSkuDetails = json_decode($oldSkuDetail,true);
        $this->oldBatchDetail = $this->oldOrderSkuDetails['batch_detail'] ?? null;
        return $this;
    }

    /**
     * @param string $updatedSkuDetail
     * @return $this
     */
    public function setUpdatedBatchDetail(string $updatedSkuDetail)
    {
        $this->updatedOrderSkuDetails = json_decode($updatedSkuDetail,true);
        $this->updatedBatchDetail = $this->updatedOrderSkuDetails['batch_detail'] ?? null;
        return $this;
    }

    /**
     * @param float $currentUnitPrice
     * @return $this
     */
    public function setCurrentUnitPrice(float $currentUnitPrice)
    {
        $this->currentUnitPrice = $currentUnitPrice;
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
     * @return bool
     */
    public function isQuantityDecreased(): bool
    {
        return $this->quantityDecreased;
    }

    /**
     * @return bool
     */
    public function isQuantityIncreased(): bool
    {
        return $this->quantityIncreased;
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
        return $this->quantityChangedValue;
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
}
