<?php namespace App\Http\Controllers;

use App\Services\PaymentLink\PaymentLinkService;
use Illuminate\Http\Request;

class PaymentLinkController extends Controller
{
    public function __construct(PaymentLinkService $paymentLinkService)
    {
        $this->paymentLinkService = $paymentLinkService;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'purpose' => 'required',
            'customer_id' => 'sometimes|integer|exists:pos_customers,id',
            'emi_month' => 'sometimes|integer|in:' . implode(',', config('emi.valid_months')),
        ]);
        return $this->paymentLinkService->store( $request);
    }
}
