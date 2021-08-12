<?php namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    /**
     * PaymentController constructor.
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(PaymentRequest $request)
    {
        return $this->paymentService->store($request);
    }
    /**
     * @OA\Delete(
     *     path="/api/v1/payment/delete}",
     *     tags={"ORDER Payment API"},
     *     summary="Order payment delete request",
     *     description="Order payment delete ",
     *     @OA\Parameter(name="order_id", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="amount", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response="true", description="Successful"),
     *     @OA\Response(response="false", description="Order Not Fount"),
     * )
     */
    public function deletePayment(Request $request)
    {
        return $this->paymentService->setOrderId($request->order_id)->setAmount($request->amount)->deletePayment();
    }
}
