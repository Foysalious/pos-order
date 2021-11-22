<?php namespace App\Services\Report;

use App\Interfaces\OrderRepositoryInterface;
use App\Services\Order\PriceCalculation;
use Illuminate\Support\Facades\App;

class SalesReport
{
    const MONTH_BASE = "month";
    protected string $from;
    protected string $to;
    protected int $partner_id;
    private $data = [];

    public function __construct(protected OrderRepositoryInterface $orderRepository,)
    {
    }

    /**
     * @param int $partner_id
     * @return SalesReport
     */
    public function setPartnerId(int $partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param string $from
     * @return SalesReport
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param string $to
     * @return SalesReport
     */
    public function setTo(string $to)
    {
        $this->to = $to;
        return $this;
    }

    public function getSalesReport()
    {
        $orders = $this->orderRepository->where('partner_id', $this->partner_id)
            ->with(['orderSkus', 'payments', 'discounts', 'customer'])
            ->whereNotNull('customer_id')
            ->whereBetween('created_at', [$this->from, $this->to])
            ->get();
        $order_calculator = App::make(PriceCalculation::class);
        $paid = 0;
        $paid_count = 0;
        $due = 0;
        $due_count = 0;
        $net_bill = 0;
        $order_count = count($orders);
        foreach ($orders as $order) {
            $order_calculator->setOrder($order);
            $net_bill = $net_bill + $order_calculator->getDiscountedPrice();
            $due = $due + $order_calculator->getDue();
            $paid = $paid + $order_calculator->getPaid();
            if ($order_calculator->getPaid() > 0)
                $paid_count = $paid_count + 1;
            if ($order_calculator->getDue() > 0)
                $due_count = $due_count + 1;
        }
        $data['paid'] = $paid;
        $data['paid_count'] = $paid_count;
        $data['due'] = $due;
        $data['due_count'] = $due_count;
        $data['net_bill'] = $net_bill;
        $data['net_bill_count'] = $order_count;
        $data['order_count'] = $order_count;
        $data['sales_stat_breakdown'] = $this->getPosOrdersBreakdownData($order_calculator, $orders, self::MONTH_BASE);
        return $data;
    }

    private function getPosOrdersBreakdownData($order_calculator, $order, $timeline_base)
    {
        $is_calculating_for_month = ($timeline_base == self::MONTH_BASE);
        $order->each(function ($order) use ($order_calculator, $is_calculating_for_month) {
            $pos_order_created_at_formatter = $is_calculating_for_month ? intval($order->created_at->format('d')) : $order->created_at->format('D');
            $day = $order->created_at->format('D');
            $date = $order->created_at->format('d M');
            if (!array_key_exists($pos_order_created_at_formatter, $this->data)){
                $this->data[$pos_order_created_at_formatter] = ["amount" => 0, "value" => 0, "date" => null, "day" => null];
            }
            $this->data[$pos_order_created_at_formatter]['amount'] = $this->data[$pos_order_created_at_formatter]['amount'] + $order_calculator->getDiscountedPrice();
            $this->data[$pos_order_created_at_formatter]['value'] = $this->data[$pos_order_created_at_formatter]['value'] + 1;
            $this->data[$pos_order_created_at_formatter]['date'] = $date;
            $this->data[$pos_order_created_at_formatter]['day'] = $day;
        });

        return collect($this->data)->values()->all();
    }

}
