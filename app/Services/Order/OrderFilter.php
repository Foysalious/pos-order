<?php namespace App\Services\Order;

use App\Interfaces\OrderRepositoryInterface;
use App\Services\Order\Constants\OrderTypes;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\Constants\Statuses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function Clue\StreamFilter\fun;

class OrderFilter
{
    protected ?string $queryString;
    protected ?int $salesChannelId;
    protected ?string $paymentStatus;
    protected int $partnerId;
    protected ?string $orderStatus;
    protected ?string $type;
    protected ?int $offset;
    protected ?int $limit;
    protected ?string $sort_by;
    protected ?string $sort_by_order;

    const SORT_BY_CREATED_AT = 'created_at';
    const SORT_BY_CUSTOMER_NAME = 'customer_name';
    const SORT_BY_ASC = 'asc';
    const SORT_BY_DESC = 'desc';

    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    )
    {
    }

    /**
     * @param string|null $queryString
     * @return OrderFilter
     */
    public function setQueryString(?string $queryString)
    {
        $this->queryString = $queryString;
        return $this;
    }

    /**
     * @param int|null $salesChannelId
     * @return OrderFilter
     */
    public function setSalesChannelId(?int $salesChannelId)
    {
        $this->salesChannelId = $salesChannelId;
        return $this;
    }

    /**
     * @param string|null $paymentStatus
     * @return OrderFilter
     */
    public function setPaymentStatus(?string $paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    /**
     * @param int $partnerId
     * @return OrderFilter
     */
    public function setPartnerId(int $partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param string|null $orderStatus
     * @return OrderFilter
     */
    public function setOrderStatus(?string $orderStatus)
    {
        $this->orderStatus = $orderStatus;
        return $this;
    }

    /**
     * @param string|null $type
     * @return OrderFilter
     */
    public function setType(?string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int|null $offset
     * @return OrderFilter
     */
    public function setOffset(?int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param int|null $limit
     * @return OrderFilter
     */
    public function setLimit(?int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param string|null $sort_by
     * @return OrderFilter
     */
    public function setSortBy(?string $sort_by)
    {
        $this->sort_by = $sort_by;
        return $this;
    }

    /**
     * @param string|null $sort_by_order
     * @return OrderFilter
     */
    public function setSortByOrder(?string $sort_by_order)
    {
        $this->sort_by_order = $sort_by_order;
        return $this;
    }

    public function getOrderListWithPagination()
    {
        $query = $this->orderRepository->where('partner_id', $this->partnerId)->with(['payments','discounts','customer','logs','orderSkus' =>function($q){
            $q->with('discount');
        }]);
        $query = $this->filterByType($query);
        $query = $this->filterByPaymentStatus($query);
        $query = $this->filterBySalesChannelId($query);
        $query = $this->filterByOrderStatus($query);
        $query = $this->filterBySearchQueryInOrder($query);
        $query = $this->filterBySearchQueryInCustomer($query);
        $query = $this->sortByCustomerName($query);
        $query = $this->sortByCreatedAt($query);
        return $query->offset($this->offset)->limit($this->limit)->get();
    }

    private function filterByType(Builder $query)
    {
        return $query->when($this->type == OrderTypes::NEW, function ($q) {
            return $q->where('status', Statuses::PENDING);
        })->when($this->type == OrderTypes::RUNNING, function ($q) {
            return $q->whereIn('status', [Statuses::PROCESSING, Statuses::SHIPPED]);
        })->when($this->type == OrderTypes::COMPLETED, function ($q){
            return $q->whereIn('status', [Statuses::COMPLETED, Statuses::CANCELLED, Statuses::DECLINED]);
        });
    }

    private function filterByOrderStatus(Builder $query)
    {
        return $query->when($this->orderStatus, function ($q) {
            return $q->where('status', $this->orderStatus);
        });
    }

    private function filterByPaymentStatus(Builder $query)
    {
        return $query->when($this->paymentStatus == PaymentStatuses::PAID, function ($q) {
            return $q->whereNotNull('paid_at');
        })->when($this->paymentStatus == PaymentStatuses::DUE, function ($q) {
            return $q->whereNull('paid_at');
        });
    }

    private function filterBySalesChannelId(Builder $query)
    {
        return $query->when($this->salesChannelId, function ($q) {
            return $q->where('sales_channel_id', $this->salesChannelId);
        });
    }

    private function sortByCustomerName(Builder $query)
    {
        return $query->when($this->sort_by == self::SORT_BY_CUSTOMER_NAME, function ($q) {
            return $q->orderBy('delivery_name', $this->sort_by_order);
        });
    }

    private function sortByCreatedAt(mixed $query)
    {
        return $query->when($this->sort_by == self::SORT_BY_CREATED_AT, function ($q) {
            return $q->orderBy('created_at', $this->sort_by_order);
        });
    }

    private function filterBySearchQueryInOrder(Builder $query)
    {
        return $query->when($this->queryString, function ($q) {
            $q->where(function($q) {
                $q->orWhere('partner_wise_order_id', 'LIKE', '%' . $this->queryString . '%' );
                $q->orWhere('delivery_name', 'LIKE', '%' .$this->queryString .'%');
                $q->orWhere('delivery_mobile', 'LIKE', '%' .$this->queryString .'%');
            });
        });
    }

    private function filterBySearchQueryInCustomer(mixed $query)
    {
        return $query->when( $this->queryString, function ($q) {
            $q->whereHas( 'customer', function ($q) {
                $q->where('partner_id', $this->partnerId);
                $q->orWhere(function ($q){
                    $q->orWhere('name', 'LIKE', '%' . $this->queryString . '%');
                    $q->orWhere('email', 'LIKE', '%' . $this->queryString . '%');
                    $q->orWhere('mobile', 'LIKE', '%' . $this->queryString . '%');
                });
            });
        });
    }

}
