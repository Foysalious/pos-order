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
        $query = $this->model->where('customer_id', $customer_id)->where('sales_channel_id', 2);
        if ($orderBy && $order) $query = $query->orderBy($orderBy, $order);
        return $query->offset($offset)->limit($limit)->get();
    }

    public function getCustomerOrderCount($customer_id)
    {
        return $this->model->where('customer_id', $customer_id)->where('sales_channel_id', 2)->get();
    }

    public function getVoucherInformation($voucher_id)
    {
        return $this->client->get('pos/v1/voucher-details/' . $voucher_id);
    }

    public function getOrderDetailsByPartner(int $partnerId, int $orderId)
    {
        return $this->model->where('partner_id', $partnerId)->with(['orderSkus' => function ($q) {
            $q->with('discount');
        }, 'discounts', 'payments'])->find($orderId);
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
        return $this->model->with(['orderSkus' => function ($q) {
            $q->with('discount');
        }, 'payments' => function ($q) {
            $q->select('id', 'amount', 'transaction_type', 'method', 'interest');
        }, 'discounts' => function ($q) {
            $q->select('id', 'order_id', 'type', 'amount', 'original_amount', 'is_percentage', 'cap', 'discount_details', 'type_id');
        },
            'customer' => function ($q) {
                $q->select('id', 'name', 'pro_pic', 'mobile');
            }])
            ->where('partner_id', $partner_id)->where('customer_id', $customer_id)
            ->when($sort_order, function ($q) use ($sort_order) {
                $q->orderBy('created_at', $sort_order);
            })->when($limit, function ($q) use ($limit) {
                $q->limit($limit);
            })->when($skip, function ($q) use ($skip) {
                $q->skip($skip);
            })->get();
    }

    public function getPartnerWiseOrderIdsFromOrderIds(array $orderIds, int $offset, int $limit)
    {
        return $this->model->whereIn('id', $orderIds)->select('id', 'partner_wise_order_id', 'sales_channel_id')->offset($offset)->limit($limit)->get();
    }
}
