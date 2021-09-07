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
     * @param mixed $skuDetail
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
            $temp_quantity = $this->sku->quantity;

            foreach ($batches as $key=>$batch) {
                $data ['batch_id'] = $batch['batch_id'];
                $data ['cost'] = $batch['cost'];

                $is_last_batch = ($key+1) == count($batches);
                if($batch['stock'] >= $temp_quantity) { //quantity less than batch size then substitute and break the loop
                    $data['quantity'] = $temp_quantity;
                    $batch_detail [] = $data;
                    break;
                }
                if ($temp_quantity > $batch['stock']  && !$is_last_batch ) { //quantity greater than batch size and not last batch then batch zero, quantity decrease from batch size
                    $data['quantity'] = $batch['stock'];
                    $temp_quantity = $temp_quantity - $batch['stock'];
                    $batch_detail [] = $data;
                    continue;
                }
                if ($is_last_batch) { // last batch then stock go negative
                    $data['quantity'] = $temp_quantity;
                    $batch_detail [] = $data;
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
