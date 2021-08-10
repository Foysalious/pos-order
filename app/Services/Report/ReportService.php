<?php namespace App\Services\Report;


use App\Http\Requests\ProductWiseReportRequest;

class ReportService
{
    public function __construct(
        protected ProductReport $productReport
    )
    {}

    public function getProductReport(int $partner_id, ProductWiseReportRequest $request)
    {
        $this->productReport->setFrom($request->from)
            ->setPartnerId($partner_id)
            ->setTo($request->to)
            ->setOrderBy($request->orderBy)
            ->setOrder($request->order)
            ->create();
    }
}
