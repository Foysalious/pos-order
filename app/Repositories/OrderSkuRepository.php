<?php namespace App\Repositories;


use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\OrderSku;
use App\Services\Order\Constants\SalesChannel;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\Statuses;

class OrderSkuRepository extends BaseRepository implements OrderSkuRepositoryInterface
{
    public function __construct(OrderSku $model)
    {
        parent::__construct($model);
    }

    public function getNotRatedOrderSkuListOfCustomer($partner_id, $customerId, int $offset, int $limit, string $order)
    {
        return $this->model->whereHas('order', function ($q) use ($customerId, $partner_id) {
            $q->where('customer_id', $customerId)->where('partner_id', $partner_id)->where('sales_channel_id', 2)->where('status', 'Completed');
        })->doesntHave('review')->offset($offset)->limit($limit)->orderBy('created_at', $order)->get();
    }

    public function getNotRatedOrderSkuListOfCustomerCount($partner_id, $customerId, string $order)
    {
        return $this->model->whereHas('order', function ($q) use ($partner_id, $customerId) {
            $q->where('customer_id', $customerId)->where('partner_id', $partner_id)->where('sales_channel_id', 2)->where('status', 'Completed');
        })->doesntHave('review')->orderBy('created_at', $order)->get();
    }


    public function updateOrderSkus($partner_id, $skus, $order_id)
    {
        foreach ($skus as $skuDetails) {
            if ($skuDetails->quantity == 0) {
                $this->model->find($skuDetails->id)->delete();
                continue;
            }
            $this->model->where('id', $skuDetails->id)
                ->where('order_id', $order_id)
                ->update([
                    'name' => $skuDetails->name,
                    'quantity' => $skuDetails->quantity
                ]);
        }
    }

    public function getTrendingProducts(int $partnerId)
    {
        return $this->model
            ->join('orders', 'orders.id', 'order_skus.order_id')
            ->where([['orders.partner_id', $partnerId], ['orders.sales_channel_id', SalesChannelIds::WEBSTORE], ['orders.status', Statuses::COMPLETED]])
            ->select('sku_id')
            ->groupBy('sku_id')
            ->orderByRaw('count(*) DESC')
            ->whereNotNull('sku_id')
            ->take(10)->pluck('sku_id');
    }
}
