<?php namespace App\Http\Controllers;


use App\Http\Requests\DataMigrationRequest;
use App\Services\DataMigration\DataMigrationService;

class DataMigrationController extends Controller
{
    private DataMigrationService $dataMigrationService;

    public function __construct(DataMigrationService $dataMigrationService)
    {
        $this->dataMigrationService = $dataMigrationService;
    }

    public function store(DataMigrationRequest $request, $partner_id)
    {
        $partner_info = $this->formatData($request->partner_info);
        $pos_orders = $this->formatData($request->pos_orders);
        $pos_order_items = $this->formatData($request->pos_order_items);
        $pos_order_payments = $this->formatData($request->pos_order_payments);
        $pos_order_discounts = $this->formatData($request->pos_order_discounts);

        return $this->dataMigrationService->setPartnerInfo($partner_info)
            ->setOrders($pos_orders)
            ->setOrderSkus($pos_order_items)
            ->setOrderPayments($pos_order_payments)
            ->setDiscounts($pos_order_discounts)
            ->migrate();
    }

    private function formatData($data)
    {
        return !is_array($data) ? json_decode($data,1) : $data;
    }

}
