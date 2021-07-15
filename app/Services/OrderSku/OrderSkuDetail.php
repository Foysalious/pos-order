<?php namespace App\Services\OrderSku;


class OrderSkuDetail
{
    protected int $sku_id;
    protected ?array $batch_detail;
    protected float $quantity;
    protected array $data;
    protected object $sku_object;


    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = json_decode($data,true);
        $this->batch_detail = $this->data['batch_detail'] ?? null;
        return $this;
    }

    public function mapData($sku, $sku_details)
    {
        $this->sku_id = $sku->id;
        $this->quantity = $sku->quantity;
        $this->sku_object = $sku;
        $this->generateOrderedSkuBatchDetail($sku_details['batches']);
        return $this;
    }

    private function generateOrderedSkuBatchDetail(mixed $batches)
    {
        if (empty($batches)) {
            $this->batch_detail = null;
        }else {
            $temp_quantity = $this->quantity;

            foreach ($batches as $key=>$batch) {
                $data ['batch_id'] = $batch['batch_id'];
                $data ['cost'] = $batch['cost'];

                $is_last_batch = ($key+1) == count($batches);
                if($batch['stock'] >= $temp_quantity) { //quantity less than batch size then substitute and break the loop
                    $data['quantity'] = $temp_quantity;
                    $this->batch_detail [] = $data;
                    break;
                }
                if ($temp_quantity > $batch['stock']  && !$is_last_batch ) { //quantity greater than batch size and not last batch then batch zero, quantity decrease from batch size
                    $data['quantity'] = $batch['stock'];
                    $temp_quantity = $temp_quantity - $batch['stock'];
                    $this->batch_detail [] = $data;
                    continue;
                }
                if ($is_last_batch) { // last batch then stock go negative
                    $data['quantity'] = $temp_quantity;
                    $this->batch_detail [] = $data;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return (array) $this->sku_object + [ 'batch_detail' => $this->batch_detail ];
    }

    /**
     * @return array|null
     */
    public function getBatchDetail(): ?array
    {
        return $this->batch_detail;
    }
}
