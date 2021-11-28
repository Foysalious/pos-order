<?php namespace App\Http\Controllers;

use App\Http\Requests\PartnerUpdateRequest;
use App\Services\Partner\PartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct(protected PartnerService $partnerService)
    {
    }

    public function show(Request $request, $partnerId)
    {
        return $this->partnerService->show($partnerId);
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
     *                  @OA\Property(property="delivery_charge", type="double"),
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
