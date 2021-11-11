<?php namespace App\Services\Report;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\PriceCalculation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class CustomerReport
{
    const MONTH_BASE = "month";
    protected string $from;
    protected string $to;
    protected int $partner_id;

    public function __construct(protected OrderRepositoryInterface $orderRepository,)
    {
    }

    /**
     * @param int $partner_id
     * @return CustomerReport
     */
    public function setPartnerId(int $partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param string $from
     * @return CustomerReport
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param string $to
     * @return CustomerReport
     */
    public function setTo(string $to)
    {
        $this->to = $to;
        return $this;
    }


    public function create()
    {
        $orders = $this->orderRepository->where('partner_id', $this->partner_id)
            ->with(['orderSkus', 'payments', 'discounts', 'customer'])
            ->whereNotNull('customer_id')
            ->whereBetween('created_at', [$this->from, $this->to])
            ->get();

        $report = [];
        $order_calculator = App::make(PriceCalculation::class);
        foreach ($orders as $order) {
            $order_calculator->setOrder($order);
            $net_bill = $order_calculator->getDiscountedPrice();
            $due = $order_calculator->getDue();
            $customer_id = $order->customer_id;

            if (!isset($report[$customer_id])) {
                $report[$customer_id]['customer_id'] = $customer_id;
                $report[$customer_id]['customer_name'] = $order->customer->name;
                $report[$customer_id]['order_count'] = 1;
                $report[$customer_id]['sales_amount'] = $net_bill;
                $report[$customer_id]['sales_due'] = $due;
            } else {
                $report[$customer_id]['order_count'] += 1;
                $report[$customer_id]['sales_amount'] += $net_bill;
                $report[$customer_id]['sales_due'] += $due;
            }
        }
        return array_values($report);

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
        $order_count = 0;
        foreach ($orders as $order) {
            $order_calculator->setOrder($order);
            $net_bill = $net_bill + $order_calculator->getDiscountedPrice();
            $due = $due + $order_calculator->getDue();
            $paid = $paid + $order_calculator->getPaid();
            if ($order_calculator->getPaid() > 0)
                $paid_count = $paid_count + 1;
            if ($order_calculator->getDue() > 0)
                $due_count = $due_count + 1;
            $order_count = $order_count + 1;
        }
        $data['paid'] = $paid;
        $data['paid_count'] = $paid_count;
        $data['due'] = $due;
        $data['due_count'] = $due_count;
        $data['net_bill'] = $net_bill;
        $data['net_bill_count'] = $order_count;
        $data['order_count'] = $order_count;
        $data['sales_stat_breakdown'] = $this->getPosOrdersBreakdownData($for = 'sales', $orders, self::MONTH_BASE);
        return $data;
    }

    private function getPosOrdersBreakdownData($for, $pos_orders, $timeline_base)
    {
        $is_calculating_for_month = ($timeline_base == self::MONTH_BASE);
        $pos_orders->each(function ($pos_order) use ($for, $is_calculating_for_month) {
            $pos_order_created_at_formatter = $is_calculating_for_month ? intval($pos_order->created_at->format('d')) : $pos_order->created_at->format('D');
            $this->data[$pos_order_created_at_formatter]['amount'] += ($for == 'sales') ? $pos_order->getNetBill() : 1;
        });
        return collect($this->data)->values()->all();
    }

    private function calculateSkusUnderProduct($sku_report, Collection $sku_details)
    {
        $data = [];
        foreach ($sku_report as $each) {
            $product = $sku_details->where('id', $each->service_id)->pluck('product')->first();
            $id = $product['id'] ?? $each->service_id;
            if (!isset($data[$id])) {
                $data[$id]['service_id'] = $product['id'];
                $data[$id]['service_name'] = $product['name'];
                $data[$id]['total_quantity'] = $each->total_quantity;
                $data[$id]['total_price'] = $each->total_price;
                $data[$id]['avg_price'] = $each->avg_price;
                $data[$id]['max_unit_price'] = $each->max_unit_price;
            } else {
                $data[$id]['total_quantity'] += $each->total_quantity;
                $data[$id]['total_price'] += $each->total_price;
                $data[$id]['avg_price'] += $each->avg_price;
                $data[$id]['max_unit_price'] = $data[$product['id']]['max_unit_price'] > $each->max_unit_price ? $data[$product['id']]['max_unit_price'] : $each->max_unit_price;
            }
        }
        return array_values($data);
    }
}
