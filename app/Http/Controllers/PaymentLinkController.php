<?php namespace App\Http\Controllers;

use App\Services\PaymentLink\PaymentLinkService;
use App\Services\PaymentLink\PaymentLinkStatistics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class PaymentLinkController extends Controller
{
    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    public function __construct(PaymentLinkService $paymentLinkService)
    {
        $this->paymentLinkService = $paymentLinkService;
    }
    /**
     * Payment Link
     * @return JsonResponse
     * @throws UnknownProperties
     * @OA\Post(
     *      path="/api/v1/payment-links",
     *      operationId="paymentlink",
     *      tags={"PAYMENT LINK API"},
     *      summary="To Create Payment Link",
     *      description="payment link for order",
     *     @OA\RequestBody(
     *     @OA\MediaType(mediaType="multipart/form-data",
     *      @OA\Schema(
     *       @OA\Property(property="amount", type="integer"),
     *       @OA\Property(property="purpose", type="string"),
     *       @OA\Property(property="pos_order_id", type="integer"),
     *       @OA\Property(property="user", type="integer"),
     *
     *          )
     * )
     * ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful",
     *                @OA\JsonContent(
     *          type="object",
     *          example={
     *           "message": "success",
     *                "link_id": 3833,
     *                "reason": "PosOrder ID: 2001842 Due payment",
     *                "type": "fixed",
     *                "status": "active",
     *                "amount": 5613,
     *                "link": "https://pl.dev-sheba.xyz/@PartnerDevuht2q99f",
     *                "emi_month": null,
     *                "interest": null,
     *                "bank_transaction_charge": null,
     *                "paid_by": "partner",
     *                "partner_profit": 0,
     *                "payer": {
     *                "id": "5",
     *                "name": "Al-Amin",
     *                "mobile": "01789547854"
     *                }
     *              }
     *        )
     *       ),
     *     )
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'purpose' => 'required',
            'customer_id' => 'sometimes|integer|exists:pos_customers,id',
            'emi_month'          => 'sometimes|integer|in:' . implode(',', config('emi.valid_months')),
            'interest_paid_by'   => 'sometimes|in:' . implode(',', PaymentLinkStatistics::paidByTypes()),
            'transaction_charge' => 'sometimes|numeric|min:' . PaymentLinkStatistics::get_payment_link_commission()
        ]);
        return $this->paymentLinkService->store( $request);
    }
}
