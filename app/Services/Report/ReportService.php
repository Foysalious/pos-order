<?php namespace App\Services\Report;


use App\Http\Requests\CustomerWiseReportRequest;
use App\Http\Requests\ProductWiseReportRequest;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;

class ReportService extends  BaseService
{
    public function __construct(
        protected ProductReport $productReport,
        protected CustomerReport $customerReport
    )
    {}

    public function getProductReport(int $partner_id, ProductWiseReportRequest $request): JsonResponse
    {
        $report = $this->productReport->setFrom($request->from)
            ->setPartnerId($partner_id)
            ->setTo($request->to)
            ->setOrderBy($request->orderBy ?? 'service_name')
            ->setOrder($request->order ?? 'ASC')
            ->create();

        return $this->success('Successful', [ 'data' => $report ]);
    }

    public function getCustomerReport(int $partner_id, CustomerWiseReportRequest $request)
    {
        $report = $this->customerReport->setPartnerId($partner_id)
            ->setFrom($request->from)
            ->setTo($request->to)
            ->create();

        return $this->success('Successful', [ 'data' => $report ]);
    }
}
