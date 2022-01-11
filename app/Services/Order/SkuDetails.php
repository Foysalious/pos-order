<?php


namespace App\Services\Order;


class SkuDetails
{
    private $skuDetails;

    /**
     * @param mixed $skuDetails
     * @return SkuDetails
     */
    public function setSkuDetails($skuDetails)
    {
        $this->skuDetails = $skuDetails;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDetails(): ?array
    {
        if (isset($this->skuDetails['combination']) || isset($this->skuDetails['product_id'])) {
            return [
                'product_id' => $this->skuDetails['product_id'],
                'combination' => $this->skuDetails['combination']
            ];
        }
        return null;

    }


}
