<?php namespace App\Services\OrderSku;


class OrderSkuDetailCreator
{
    protected $sku;
    protected $skuDetails;


    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @param $skuDetails
     * @return OrderSkuDetailCreator
     */
    public function setSkuDetails($skuDetails)
    {
        $this->skuDetails = $skuDetails;
        return $this;
    }


    private function generateOrderedSkuBatchDetail(array $batches): array
    {
        if (empty($batches)) {
            $batch_detail = [];
        }else {
            $quantity = $this->sku->quantity;

            foreach ($batches as $key=>$batch) {
                $batch_data ['batch_id'] = $batch['batch_id'];
                $batch_data ['cost'] = $batch['cost'];

                $is_last_batch = ($key+1) == count($batches);
                if ($quantity > $batch['stock']  && !$is_last_batch ) { //quantity greater than batch size and not last batch then batch zero, quantity decrease from batch size
                    $batch_data['quantity'] = $batch['stock'];
                    $quantity = $quantity - $batch['stock'];
                    $batch_detail [] = $batch_data;
                    continue;
                }
                if($batch['stock'] >= $quantity) { //quantity less than batch size then substitute and break the loop
                    $batch_data['quantity'] = $quantity;
                    $batch_detail [] = $batch_data;
                    break;
                }
                if ($is_last_batch) { // last batch then stock go negative
                    $batch_data['quantity'] = $quantity;
                    $batch_detail [] = $batch_data;
                }
            }
        }
        return $batch_detail;
    }

    /**
     * @return array
     */
    public function create(): array
    {
        $batch_detail = $this->generateOrderedSkuBatchDetail($this->skuDetails['batches']);
        return (array) $this->sku + [ 'batch_detail' => $batch_detail ];
    }
}
