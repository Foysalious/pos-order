<?php


namespace App\Services\OrderSku;


class BatchManipulator
{
    protected ?array $batchDetail;
    protected array $oldBatchDetails;
    protected float $updatedQuantity;
    protected float $previousQuantity;
    protected array $skuBatch;

    protected array $updatedBatchDetail;

    /**
     * @param mixed $batch_detail
     */
    public function setBatchDetail(?string $batch_detail) : BatchManipulator
    {
        $this->batchDetail = json_decode($batch_detail,true);
        $this->oldBatchDetails = $this->batchDetail;
        return $this;
    }

    /**
     * @param float $updatedQuantity
     * @return BatchManipulator
     */
    public function setUpdatedQuantity(float $updatedQuantity)
    {
        $this->updatedQuantity = $updatedQuantity;
        return $this;
    }

    public function getUpdatedBatchDetail(): bool|string
    {
       return json_encode($this->updatedBatchDetail);
    }

    /**
     * @param float $previousQuantity
     * @return BatchManipulator
     */
    public function setPreviousQuantity(float $previousQuantity) : BatchManipulator
    {
        $this->previousQuantity = $previousQuantity;
        return $this;
    }

    public function updateBatchDetail() : BatchManipulator
    {
        $new_quantity = $this->updatedQuantity;
        $old_quantity = $this->previousQuantity;

        if($new_quantity > $old_quantity) {
            $quantity_added = $new_quantity - $old_quantity;
            $this->updatedBatchDetail = $this->addItemInBatchDetail($quantity_added);
        } else {
            $quantity_reduced = $old_quantity-$new_quantity;
            $this->updatedBatchDetail = $this->decreaseItemFromBatchDetail($quantity_reduced);
        }
        return $this;
    }

    public function setSkuBatch($sku_batch) : BatchManipulator
    {
        $this->skuBatch = $sku_batch;
        return $this;
    }

    private function decreaseItemFromBatchDetail($quantity)
    {
        $batch_detail = collect($this->oldBatchDetails)->sortByDesc('batch_id');

        $batch_detail = $batch_detail->map(function ($batch) use (&$quantity){
            if($quantity >= $batch['quantity']) {
                $quantity = $quantity - $batch['quantity'];
                $batch['quantity'] = 0;
            }
            elseif ($quantity < $batch['quantity']) {
                $batch['quantity'] = $batch['quantity'] - $quantity;
                $quantity = 0;
            }
            return $batch;
        });
        return $batch_detail->where('quantity', '<>', 0)->sortBy('batch_id')->all();
    }

    private function addItemInBatchDetail($quantity)
    {
        $batches = $this->skuBatch;
        $temp_quantity = $quantity;
        $batch_detail = [];
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
        return $this->mergeBatchDetailIfSame($batch_detail, $this->oldBatchDetails);
    }

    private function mergeBatchDetailIfSame(array $added_batch_detail, array $old_batch_details): array
    {
        $old_batch_details = collect($old_batch_details);
        $new_batch_details = [];

        foreach ($added_batch_detail as $each) {
            $index = $old_batch_details->search(function ($batch) use ($each){
                return ($batch['batch_id'] == $each['batch_id'] && $batch['cost'] == $each['cost']) ;
            });
            if( $index !== false){
                $new_batch_details [] = [
                    'batch_id' => $each['batch_id']  ,
                    'cost' => $each['cost'],
                    'quantity' => $old_batch_details[$index]['quantity'] + $each['quantity'],
                ];
                unset($old_batch_details[$index]);
            } else {
                $new_batch_details [] = $each;
            }
        }
        return array_merge($new_batch_details,$old_batch_details->toArray());
    }

    /**
     * @return array
     */
    public function getBatchDetails(): array
    {
        return $this->oldBatchDetails;
    }
}
