<?php namespace App\Services\Report;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Services\Inventory\InventoryServerClient;
use Illuminate\Support\Collection;

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
            ->with(['orderSkus','payments','discounts'])
            ->whereNotNull('customer_id')
            ->whereBetween('created_at', [$this->from, $this->to])
            ->get();

        $report = [];

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
