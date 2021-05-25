<?php namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\Order\Constants\Statuses;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    private function getSearchingQuery($partner_id, $orderSearch)
    {
        $order_id = $orderSearch->getOrderId();
        $customer_name = $orderSearch->getCustomerName();
        $sales_channel_id = $orderSearch->getSalesChannelId();

        return $this->model->where('partner_id', $partner_id)->whereHas('customer', function ($query) use ($customer_name) {
            $query->where('name', 'like', '%'. $customer_name .'%');
        })->when($order_id, function ($query) use ($order_id) {
            return $query->where('partner_wise_order_id', $order_id);
        })->when($sales_channel_id, function ($query) use ($sales_channel_id) {
            return $query->where('sales_channel_id', $sales_channel_id);
        });
    }

    private function getTypeFilterResult($type, $searchQueryResult)
    {
        return $searchQueryResult->when($type == Statuses::ORDER_FILTER_TYPE['new'], function($query) use ($type){
            return $query->where('status', Statuses::PENDING);
        })->when($type == Statuses::ORDER_FILTER_TYPE['running'], function($query) use ($type){
            return $query->whereIn('status', [Statuses::PROCESSING, Statuses::SHIPPED]);
        })->when($type == Statuses::ORDER_FILTER_TYPE['completed'], function($query) use ($type){
            return $query->whereIn('status', [Statuses::COMPLETED, Statuses::CANCELLED, Statuses::DECLINED]);
        });
    }

    private function getOrderStatusFilterResult($orderStatus, $typeFilterResult)
    {
        return $typeFilterResult->when($orderStatus, function($query) use ($orderStatus){
            return $query->where('status', $orderStatus);
        });
    }

    private function getPaymentStatusFilterResult($paymentStatus, $orderStatusFilterResult)
    {
        return $orderStatusFilterResult->when($paymentStatus == Statuses::PAYMENT_STATUS['paid'], function($query) use ($paymentStatus){
            return $query->whereNotNull('closed_and_paid_at');
        })->when($paymentStatus == Statuses::PAYMENT_STATUS['due'], function($query) use ($paymentStatus){
            return $query->whereNull('closed_and_paid_at');
        });
    }

    private function getFilteringQuery($orderFilterParams, $searchQueryResult)
    {
        $type = $orderFilterParams->getType();
        $orderStatus = $orderFilterParams->getOrderStatus();
        $paymentStatus = $orderFilterParams->getPaymentStatus();

        $typeFilterResult = $this->getTypeFilterResult($type, $searchQueryResult);
        $orderStatusFilterResult = $this->getOrderStatusFilterResult($orderStatus, $typeFilterResult);
        return $this->getPaymentStatusFilterResult($paymentStatus, $orderStatusFilterResult);
    }

    public function getOrderListWithPagination($offset, $limit, $partner_id, $orderSearch, $orderFilter)
    {
        $searchQueryOrderList = $this->getSearchingQuery($partner_id, $orderSearch);
        $filterQueryOrderList = $this->getFilteringQuery($orderFilter, $searchQueryOrderList);
        $orderList = $filterQueryOrderList ? $filterQueryOrderList : $searchQueryOrderList;
        return $orderList->offset($offset)->limit($limit)->latest()->get();
    }
}
