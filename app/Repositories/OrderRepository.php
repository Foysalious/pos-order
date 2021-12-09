<?php namespace App\Repositories;

use App\Helper\TimeFrame;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Order\Constants\SalesChannelIds;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    protected ApiServerClient $client;

    public function __construct(Order $model, ApiServerClient $client)
    {
        $this->client = $client;
        parent::__construct($model);
    }

    public function getCustomerOrderList($customer_id, $offset, $limit, $orderBy, $order)
    {
        $query = $this->model->where('customer_id', $customer_id);
        if ($orderBy && $order) $query = $query->orderBy($orderBy, $order);
        return $query->offset($offset)->limit($limit)->get();
    }

    public function getCustomerOrderCount($customer_id)
    {
        return $this->model->where('customer_id', $customer_id)->get();
    }

    public function getVoucherInformation($voucher_id)
    {
        return $this->client->get('pos/v1/voucher-details/' . $voucher_id);
    }

    public function getOrderDetailsByPartner(int $partnerId, int $orderId)
    {
        return $this->model->where('partner_id', $partnerId)->with(['orderSkus' => function ($q) {
            $q->with('discount');
        }, 'discounts', 'payments'])->with('orderSkus')->find($orderId);
    }

    public function getOrderStatusStatByPartner(int $partnerId, $salesChannelIds = [SalesChannelIds::POS, SalesChannelIds::WEBSTORE])
    {
        return $this->model->where('partner_id', $partnerId)->whereIn('sales_channel_id', $salesChannelIds)
            ->select(DB::raw('count(*) as count, status'))
            ->groupBy('status')->get();
    }

    public function getOrdersBetweenDatesByPartner(int $partnerId, TimeFrame $time_frame, $salesChannelIds = [SalesChannelIds::POS, SalesChannelIds::WEBSTORE])
    {
        return $this->model->where('partner_id', $partnerId)->whereIn('sales_channel_id', $salesChannelIds)
            ->whereBetween('created_at', $time_frame->getArray())
            ->with('orderSkus', 'payments', 'discounts')
            ->get();
    }


    public function getAllOrdersOfPartnersCustomer(int $partner_id, $customer_id, $sort_order = false, $limit = false, $skip = false)
    {
        return $this->model->with(['orderSkus' => function($q){
            $q->with('discount');
        }, 'payments', 'discounts'])
            ->where('partner_id', $partner_id)->where('customer_id', $customer_id)
            ->when($sort_order, function ($q) use ($sort_order){
                $q->orderBy('created_at', $sort_order);
            })->when($limit, function ($q) use ($limit){
                $q->limit($limit);
            })->when($skip, function ($q) use ($skip){
                $q->skip($skip);
            })->get();
    }
}
