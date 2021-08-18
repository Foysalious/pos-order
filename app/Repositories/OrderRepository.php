<?php namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Order\Constants\OrderTypes;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\OrderSearch;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    protected ApiServerClient $client;

    public function __construct(Order $model, ApiServerClient $client)
    {
        $this->client = $client;
        parent::__construct($model);
    }

    public function getOrderListWithPagination($offset, $limit, $partner_id, $orderSearch, $orderFilter)
    {

        $query = $this->model->with(['orderSkus','payments','discounts','customer'])->where('partner_id', $partner_id);

        $query = $this->getSearchingQuery($partner_id, $orderSearch);
        $filterQueryOrderList = $this->getFilteringQuery($orderFilter, $searchQueryOrderList);
        $orderList = $filterQueryOrderList ? $filterQueryOrderList : $searchQueryOrderList;
        dd($orderList->get()->count());
        return $orderList->offset($offset)->limit($limit)->latest()->get();
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


    private function getSearchingQuery(int $partner_id, OrderSearch $orderSearch)
    {
        $query_string = $orderSearch->getQueryString();
        $sales_channel_id = $orderSearch->getSalesChannelId();

        return $this->model->with(['orderSkus','payments','discounts','customer'])
            ->where('partner_id', $partner_id)
            ->whereHas('customer', function ($query) use ($query_string,$partner_id) {
                $query->when($query_string, function ($query) use ($query_string) {
                    $query->Where('name', 'LIKE', '%' . $query_string . '%');
                    $query->orWhere('email', 'LIKE', '%' . $query_string . '%');
                    $query->orWhere('mobile', 'LIKE', '%' . $query_string . '%');
                });
            })->when($query_string, function ($query) use ($query_string) {
                return $query->orWhere('partner_wise_order_id', 'LIKE', '%' .$query_string .'%');
            })->when($sales_channel_id, function ($query) use ($sales_channel_id) {
                return $query->where('sales_channel_id', $sales_channel_id);
            });
    }

    private function getTypeFilterResult($type, $searchQueryResult)
    {
        return $searchQueryResult->when($type == OrderTypes::NEW, function ($query) use ($type) {
            return $query->where('status', Statuses::PENDING);
        })->when($type == OrderTypes::RUNNING, function ($query) use ($type) {
            return $query->whereIn('status', [Statuses::PROCESSING, Statuses::SHIPPED]);
        })->when($type == OrderTypes::COMPLETED, function ($query) use ($type) {
            return $query->whereIn('status', [Statuses::COMPLETED, Statuses::CANCELLED, Statuses::DECLINED]);
        });
    }

    private function getOrderStatusFilterResult($orderStatus, $typeFilterResult)
    {
        return $typeFilterResult->when($orderStatus, function ($query) use ($orderStatus) {
            return $query->where('status', $orderStatus);
        });
    }

    private function getPaymentStatusFilterResult($paymentStatus, $orderStatusFilterResult)
    {
        return $orderStatusFilterResult->when($paymentStatus == PaymentStatuses::PAID, function ($query) use ($paymentStatus) {
            return $query->whereNotNull('closed_and_paid_at');
        })->when($paymentStatus == PaymentStatuses::DUE, function ($query) use ($paymentStatus) {
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

    public function getVoucherInformation($voucher_id)
    {
        return $this->client->setBaseUrl()->get('pos/v1/voucher-details/'. $voucher_id);
    }

    public function getOrderDetailsByPartner(int $partnerId, int $orderId)
    {
        return $this->model->where('partner_id', $partnerId)->with(['orderSkus' => function($q) {
            $q->with('discount');
        }, 'discounts', 'payments'])->find($orderId);
    }
}
