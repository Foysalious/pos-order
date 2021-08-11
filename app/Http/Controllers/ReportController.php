<?php namespace App\Http\Controllers;


use App\Http\Requests\CustomerWiseReportRequest;
use App\Http\Requests\ProductWiseReportRequest;
use App\Services\Report\ReportService;

class ReportController
{
    public function __construct(
        protected ReportService $reportService
    )
    {}

    public function getProductWise(int $partner_id, ProductWiseReportRequest $request )
    {
        return $this->reportService->getProductReport($partner_id, $request);
    }

    public function getCustomerWise(int $partner_id, CustomerWiseReportRequest $request )
    {
        return $this->reportService->getCustomerReport($partner_id, $request);
    }
}
