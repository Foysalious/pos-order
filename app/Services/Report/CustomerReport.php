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
    protected string $from;
    protected string $to;
    protected int $partner_id;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
    )
    {}

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
            ->with(['orderSkus','payments','discounts','customer'])
            ->whereNotNull('customer_id')
            ->whereNotIn('status', [Statuses::DECLINED, Statuses::CANCELLED])
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


    private function calculateSkusUnderProduct($sku_report, Collection $sku_details)
    {
        $data = [];
        foreach ($sku_report as $each) {
            $product = $sku_details->where('id', $each->service_id)->pluck('product')->first();
            $id = $product['id'] ?? $each->service_id;
            if(!isset($data[$id])){
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
