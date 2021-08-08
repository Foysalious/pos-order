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

    public function delete(Request $request)
    {
        return $this->paymentService->setOrderId($request->order_id)->setAmount($request->amount)->deletePayment();
    }
}
