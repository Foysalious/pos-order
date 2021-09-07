<?php namespace App\Services\Order;

use App\Interfaces\OrderRepositoryInterface;
use App\Services\Order\Constants\OrderTypes;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\Constants\Statuses;

class OrderSearch
{
    protected ?string $queryString;
    protected ?int $salesChannelId;
    protected ?string $paymentStatus;
    protected int $partnerId;
    protected ?string $orderStatus;
    protected ?string $type;
    protected ?int $offset;
    protected ?int $limit;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    )
    {
    }

    /**
     * @param string|null $queryString
     * @return OrderSearch
     */
    public function setQueryString(?string $queryString)
    {
        $this->queryString = $queryString;
        return $this;
    }

    /**
     * @param int|null $salesChannelId
     * @return OrderSearch
     */
    public function setSalesChannelId(?int $salesChannelId)
    {
        $this->salesChannelId = $salesChannelId;
        return $this;
    }

    /**
     * @param string|null $paymentStatus
     * @return OrderSearch
     */
    public function setPaymentStatus(?string $paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    /**
     * @param int $partnerId
     * @return OrderSearch
     */
    public function setPartnerId(int $partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param string|null $orderStatus
     * @return OrderSearch
     */
    public function setOrderStatus(?string $orderStatus)
    {
        $this->orderStatus = $orderStatus;
        return $this;
    }

    /**
     * @param string|null $type
     * @return OrderSearch
     */
    public function setType(?string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int|null $offset
     * @return OrderSearch
     */
    public function setOffset(?int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param int|null $limit
     * @return OrderSearch
     */
    public function setLimit(?int $limit)
    {
        $this->limit = $limit;
        return $this;
    }


    public function getOrderListWithPagination()
    {
        $query = $this->orderRepository->where('partner_id', $this->partnerId)->with(['orderSkus','payments','discounts','customer']);
        $query = $this->filterBySalesChannelId($query);
        $query = $this->filterByType($query);
        $query = $this->filterByOrderStatus($query);
        $query = $this->filterByPaymentStatus($query);
        $query = $this->filterBySearchQuery($query);
        return $query->offset($this->offset)->limit($this->limit)->latest()->get();
    }

    private function filterByType($query)
    {
        return $query->when($this->type == OrderTypes::NEW, function ($q) {
            return $q->where('status', Statuses::PENDING);
        })->when($this->type == OrderTypes::RUNNING, function ($q) {
            return $q->whereIn('status', [Statuses::PROCESSING, Statuses::SHIPPED]);
        })->when($this->type == OrderTypes::COMPLETED, function ($q){
            return $q->whereIn('status', [Statuses::COMPLETED, Statuses::CANCELLED, Statuses::DECLINED]);
        });
    }

    private function filterByOrderStatus($query)
    {
        return $query->when($this->orderStatus, function ($q) {
            return $q->where('status', $this->orderStatus);
        });
    }

    private function filterByPaymentStatus($query)
    {
        return $query->when($this->paymentStatus == PaymentStatuses::PAID, function ($q) {
            return $q->whereNotNull('closed_and_paid_at');
        })->when($this->paymentStatus == PaymentStatuses::DUE, function ($q) {
            return $q->whereNull('closed_and_paid_at');
        });
    }

    private function filterBySearchQuery($query)
    {
        return $query->when(is_integer($this->queryString), function ($q) {
            $q->where('partner_wise_order_id', 'LIKE', '%' .$this->queryString .'%');
        })->whereHas('customer', function ($q) {
            $q->when( $this->queryString, function ($q) {
                $q->where('name', 'LIKE', '%' . $this->queryString . '%');
                $q->orWhere('email', 'LIKE', '%' . $this->queryString . '%');
                $q->orWhere('mobile', 'LIKE', '%' . $this->queryString . '%');
            });
        });
    }

    private function filterBySalesChannelId($query)
    {
        return $query->when($this->salesChannelId, function ($q) {
            return $q->where('sales_channel_id', $this->salesChannelId);
        });
    }

}
