<?php namespace App\Services\Report;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;

class ProductReport
{
    protected string $from;
    protected string $to;
    protected string $order;
    protected string $orderBy;
    protected int $partner_id;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderSkuRepositoryInterface $orderSkuRepository
    )
    {}

    /**
     * @param int $partner_id
     * @return ProductReport
     */
    public function setPartnerId(int $partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }
    /**
     * @param string $from
     * @return ProductReport
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param string $to
     * @return ProductReport
     */
    public function setTo(string $to)
    {
        $this->to = $to;
        return $this;
    }


    /**
     * @param string $order
     * @return ProductReport
     */
    public function setOrder(string $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param string $orderBy
     * @return ProductReport
     */
    public function setOrderBy(string $orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }


    public function create()
    {
        $orders = $this->orderRepository->where('partner_id', $this->partner_id)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->select('id')
            ->pluck('id');
        $sku_report = $this->orderSkuRepository->whereIn('order_id', $orders)
            ->selectRaw("sku_id,CAST(SUM(quantity) as UNSIGNED) total_quantity ,CAST((SUM((quantity * unit_price))) as DECIMAL(10,2))  total_price,CAST(((SUM((unit_price * quantity))/SUM(quantity))) as DECIMAL(10,2)) avg_price,CAST((MAX(unit_price)) as DECIMAL(10,2)) as max_unit_price")
            ->groupBy('sku_id')
            ->get();
        $sku_ids = $sku_report->whereNotNull('sku_id')->pluck('sku_id');
        dd($sku_ids);
    }
}
