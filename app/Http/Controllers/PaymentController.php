<?php namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
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
     * Delete customer
     * @param Request $request
     *
     * @OA\Post(
     *     path="/api/v1/payment/delete",
     *     tags={"Order Payment delete API"},
     *     summary="To Delete a order payment",
     *     description="Delete order payment ",
     *     @OA\Parameter(name="order_id", description="order id", required=true, in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="amount", description="amount", required=true, in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Successful")
     * )
     */
    public function deletePayment(Request $request)
    {
        return $this->paymentService->setOrderId($request->order_id)->setAmount($request->amount)->deletePayment();
    }
}
