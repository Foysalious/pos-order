<?php namespace App\Http\Controllers;

use App\Http\Requests\PartnerUpdateRequest;
use App\Services\DataMigration\DataMigrationService;
use App\Services\Partner\PartnerService;
use App\Traits\ResponseAPI;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataMigrationController extends Controller
{
    use ResponseAPI;
    private DataMigrationService $dataMigrationService;

    public function __construct(DataMigrationService $dataMigrationService, protected PartnerService $partnerService)
    {
        $this->dataMigrationService = $dataMigrationService;
    }

    /**
     * @throws Exception
     */
    public function store(Request $request, $partner_id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $partner_info = $this->formatData($request->partner_info);
            $pos_orders = $this->formatData($request->pos_orders);
            $pos_order_items = $this->formatData($request->pos_order_items);
            $pos_order_payments = $this->formatData($request->pos_order_payments);
            $pos_order_discounts = $this->formatData($request->pos_order_discounts);
            $pos_order_logs = $this->formatData($request->pos_order_logs);
            $customers = $this->formatData($request->pos_customers);
            $this->dataMigrationService->setPartnerInfo($partner_info)
                ->setOrders($pos_orders)
                ->setOrderSkus($pos_order_items)
                ->setOrderPayments($pos_order_payments)
                ->setDiscounts($pos_order_discounts)
                ->setOrderLogs($pos_order_logs)
                ->setCustomers($customers)
                ->migrate();
            DB::commit();
        } catch (Exception $e){
            DB::rollBack();
            throw $e;
        }
        return $this->success();

    }

    private function formatData($data)
    {
        return !is_array($data) ? json_decode($data,1) : $data;
    }


    /**
     *
     *     @OA\Put (
     *     path="/api/v1/partners/{partner}",
     *     summary="Sync partners pos setting",
     *     tags={"Partner Update API"},
     *     @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *          @OA\MediaType(mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  @OA\Property(property="name", type="String"),
     *                  @OA\Property(property="sub_domain", type="String"),
     *                  @OA\Property(property="sms_invoice", type="boolean"),
     *                  @OA\Property(property="auto_printing", type="boolean"),
     *                  @OA\Property(property="printer_name", type="String"),
     *                  @OA\Property(property="printer_model", type="String"),
     *             )
     *         )
     *      ),
     *     @OA\Response(response=200, description="Successful",
     *          @OA\JsonContent(
     *          type="object",
     *          example={ "message": "Successful" }
     *       ),
     *     )
     * )
     *
     * @param $partner_id
     * @param PartnerUpdateRequest $request
     * @return JsonResponse
     */
    public function updatePartnersTable($partner_id, PartnerUpdateRequest $request)
    {
        return $this->partnerService->updatePartner($partner_id,$request);
    }
}
