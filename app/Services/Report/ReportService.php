<?php namespace App\Services\Report;


use App\Http\Requests\ProductWiseReportRequest;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;

class ReportService extends  BaseService
{
    public function __construct(
        protected ProductReport $productReport
    )
    {}

    public function getProductReport(int $partner_id, ProductWiseReportRequest $request): JsonResponse
    {
        $report = $this->productReport->setFrom($request->from)
            ->setPartnerId($partner_id)
            ->setTo($request->to)
            ->setOrderBy($request->orderBy)
            ->setOrder($request->order ?? 'ASC')
            ->create();

        return $this->success('Success', [ 'data' => $report ]);
    }
}
