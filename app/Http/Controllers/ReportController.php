<?php namespace App\Http\Controllers;


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
}
