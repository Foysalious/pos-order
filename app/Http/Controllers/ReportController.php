<?php namespace App\Http\Controllers;


use App\Http\Requests\CustomerWiseReportRequest;
use App\Http\Requests\ProductWiseReportRequest;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController
{
    public function __construct(
        protected ReportService $reportService
    )
    {}

    /**
     * @OA\Get(
     *      path="/api/v1/partners/{partner_id}/reports/product-wise",
     *      operationId="getReportProductWise",
     *      tags={"Report APIs"},
     *      summary="Api to get product selling report",
     *      description="Api to get product sellings report",
     *      @OA\Parameter(name="partner_id",description="partner id",required=true,in="path", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="from",description="From Date (YYYY-MM-DD)",required=true,in="query", @OA\Schema(type="string")),
     *      @OA\Parameter(name="to",description="To Date ((YYYY-MM-DD))",required=true,in="query", @OA\Schema(type="string")),
     *      @OA\Parameter(name="order",description="ASC or DESC",required=false,in="query", @OA\Schema(type="string")),
     *      @OA\Parameter(name="orderBy",description="Order by like service_name",required=false,in="query", @OA\Schema(type="string")),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *             example={{"message":"Successful","data":{{"service_id":null,"service_name":"custom","total_quantity":8,"total_price":"400.00","avg_price":"50.00","max_unit_price":"50.00"},{"service_id":null,"service_name":"Custom-1","total_quantity":5,"total_price":"500.00","avg_price":"100.00","max_unit_price":"100.00"},{"service_id":null,"service_name":"Fring Production","total_quantity":3,"total_price":"270.00","avg_price":"90.00","max_unit_price":"90.00"},{"service_id":null,"service_name":"Horlics 500ml","total_quantity":3,"total_price":"270.00","avg_price":"90.00","max_unit_price":"90.00"},{"service_id":null,"service_name":"name","total_quantity":110,"total_price":"5500.00","avg_price":"50.00","max_unit_price":"50.00"},{"service_id":null,"service_name":"name of sku","total_quantity":3,"total_price":"220.00","avg_price":"73.33","max_unit_price":"80.00"},{"service_id":null,"service_name":"Quick Sell Item","total_quantity":2,"total_price":"100.00","avg_price":"50.00","max_unit_price":"50.00"},{"service_id":null,"service_name":"Shirt","total_quantity":10,"total_price":"1000.00","avg_price":"100.00","max_unit_price":"100.00"},{"service_id":null,"service_name":"\u0986\u0987\u099f\u09c7\u09ae","total_quantity":173,"total_price":"11126084.00","avg_price":"64312.62","max_unit_price":"8888777.00"}}}}
     *          ))
     *     )
     */
    public function getProductWise(int $partner_id, ProductWiseReportRequest $request ): JsonResponse
    {
        return $this->reportService->getProductReport($partner_id, $request);
    }


    /**
     * @OA\Get(
     *      path="/api/v1/partners/{partner_id}/reports/customer-wise",
     *      operationId="getReportCustomerWise",
     *      tags={"Report APIs"},
     *      summary="Api to get customer wise report",
     *      description="Api to get product sellings report",
     *      @OA\Parameter(name="partner_id",description="partner id",required=true,in="path", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="from",description="From Date (YYYY-MM-DD)",required=true,in="query", @OA\Schema(type="string")),
     *      @OA\Parameter(name="to",description="To Date ((YYYY-MM-DD))",required=true,in="query", @OA\Schema(type="string")),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *             example={{"message":"Successful","data":{{"customer_id":"610121e0e787a4001392bb75","customer_name":"Customer 1","order_count":6,"sales_amount":2440,"sales_due":2440},{"customer_id":"5","customer_name":"Al-Amin","order_count":368,"sales_amount":10946961.8,"sales_due":1017684.5},{"customer_id":"1314","customer_name":"Nafeeur","order_count":31,"sales_amount":4780,"sales_due":3180},{"customer_id":"1","customer_name":"Banedict","order_count":390,"sales_amount":309649,"sales_due":303525}}}}
     *          ))
     *     )
     */
    public function getCustomerWise(int $partner_id, CustomerWiseReportRequest $request ): JsonResponse
    {
        return $this->reportService->getCustomerReport($partner_id, $request);
    }
    public function getSalesReport(int $partner, CustomerWiseReportRequest $request)
    {
        return $this->reportService->getSalesReport($partner,$request);
    }
}
