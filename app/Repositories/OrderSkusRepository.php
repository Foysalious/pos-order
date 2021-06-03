<?php namespace App\Repositories;


use App\Interfaces\OrderSkusRepositoryInterface;
use App\Models\OrderSku;

class OrderSkusRepository extends BaseRepository implements OrderSkusRepositoryInterface
{
    public function __construct(OrderSku $model)
    {
        parent::__construct($model);
    }

    public function updateOrderSkus($partner_id, $skus, $order_id)
    {
        foreach ($skus as $skuDetails)
        {
            if($skuDetails->quantity == 0) {
                $this->model->find($skuDetails->id)->delete();
                continue;
            }
            $this->model->where('id', $skuDetails->id)
                ->where('order_id', $order_id)
                ->update([
                    'name'     => $skuDetails->name,
                    'quantity' => $skuDetails->quantity
                ]);
        }
    }
}
