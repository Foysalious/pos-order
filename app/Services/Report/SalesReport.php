<?php namespace App\Services\Report;

use App\Interfaces\OrderRepositoryInterface;
use App\Services\Order\PriceCalculation;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class SalesReport
{
    protected string $from;
    protected string $to;
    protected int $partner_id;
    private $data = [];

    public function __construct(protected OrderRepositoryInterface $orderRepository, private PriceCalculation $orderCalculation)
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
       $from= Carbon::parse($this->from." 00:00:00")->format('Y-m-d H:s:i');
       $to= Carbon::parse($this->to." 11:59:00")->format('Y-m-d H:s:i');
        $orders = $this->orderRepository->builder()->where('partner_id', $this->partner_id)
            ->with(['orderSkus' => function($q){
                $q->with('discount');
            }, 'payments', 'discounts', 'customer'])
            ->whereBetween('created_at', [$from, $to])
            ->get();
        $paid = 0;
        $paid_count = 0;
        $due = 0;
        $due_count = 0;
        $net_bill = 0;
        $order_count = count($orders);
        $times = CarbonPeriod::create($this->from, $this->to);
        $time_duration = [];
        foreach ($times as $time) {
            $time_duration[$time->format('Y-m-d')] = ["amount" => 0, "orders" => 0, "date" => $time->format('d M'), "day" => $time->format('D')];
        }
        foreach ($orders as $order) {
            $this->orderCalculation->setOrder($order);

            $time_duration[$order->created_at->toDateString()]['amount']+=$this->orderCalculation->getDiscountedPrice();
            $time_duration[$order->created_at->toDateString()]['orders']+=1;
            $order['discounted_price'] = $this->orderCalculation->getDiscountedPrice();
            $net_bill = $net_bill + $order['discounted_price'];
            $due = $due + $this->orderCalculation->getDue();
            $paid = $paid + $this->orderCalculation->getPaid();
            if ($this->orderCalculation->getPaid() > 0)
                $paid_count = $paid_count + 1;
            if ($this->orderCalculation->getDue() > 0)
                $due_count = $due_count + 1;
        }
        $data['paid'] = $paid;
        $data['paid_count'] = $paid_count;
        $data['due'] = $due;
        $data['due_count'] = $due_count;
        $data['net_bill'] = $net_bill;
        $data['net_bill_count'] = $order_count;
        $data['order_count'] = $order_count;
        $data['sales_stat_breakdown'] = $time_duration;
        return $data;
    }

}
