<?php namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Order\Constants\OrderTypes;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\OrderFilter;
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
        return $this->client->setBaseUrl()->get('pos/v1/voucher-details/'. $voucher_id);
    }

    public function getOrderDetailsByPartner(int $partnerId, int $orderId)
    {
        return $this->model->where('partner_id', $partnerId)->with(['orderSkus' => function($q) {
            $q->with('discount');
        }, 'discounts', 'payments'])->find($orderId);
    }

    public function getOrderStatusStatByPartner(int $partnerId)
    {
        return $this->model->where('partner_id', $partnerId)
            ->select(DB::raw('count(*) as count, status'))
            ->groupBy('status')->get();
    }
}
