<?php namespace App\Services\Report;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Services\Inventory\InventoryServerClient;
use Illuminate\Support\Collection;

class ProductReport
{
    protected string $from;
    protected string $to;
    protected string $order = 'ASC';
    protected string $orderBy = 'service_name';
    protected int $partner_id;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderSkuRepositoryInterface $orderSkuRepository,
        protected InventoryServerClient $client
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
            ->selectRaw("sku_id as service_id,name as service_name,CAST(SUM(quantity) as UNSIGNED) total_quantity ,CAST((SUM((quantity * unit_price))) as DECIMAL(10,2))  total_price,CAST(((SUM((unit_price * quantity))/SUM(quantity))) as DECIMAL(10,2)) avg_price,CAST((MAX(unit_price)) as DECIMAL(10,2)) as max_unit_price")
            ->orderBy($this->orderBy,$this->order)
            ->groupBy(['service_id','service_name'])
            ->get();
        $sku_ids = $sku_report->whereNotNull('service_id')->pluck('service_id')->unique();
        $sku_details = collect($this->getSkuDetails($sku_ids));

        $items_without_id = $sku_report->whereNull('service_id')->toArray();
        $product_report = $this->calculateSkusUnderProduct( $sku_report->whereNotNull('service_id') , $sku_details);
        return array_values($items_without_id) + array_values($product_report);
    }

    private function getSkuDetails(Collection $sku_ids)
    {
        if($sku_ids->isEmpty()) {
            return [];
        } else {
            $url = 'api/v1/partners/' . $this->partner_id . '/skus?skus=' . json_encode($sku_ids) . '&with_deleted=1';
            $response = $this->client->setBaseUrl()->get($url);
            return $response['skus'];
        }
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
