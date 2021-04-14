<?php namespace App\Services\Payment;

use App\Http\Requests\PaymentRequest;
use App\Services\BaseService;
use Illuminate\Http\Request;

class PaymentService extends BaseService
{
    private Creator $creator;

    /**
     * PaymentService constructor.
     * @param Creator $creator
     */
    public function __construct(Creator $creator)
    {
        $this->creator = $creator;
    }

    public function store(PaymentRequest $request)
    {
        $this->creator->setOrderId($request->order_id)
            ->setAmount($request->amount)
            ->setTransactionType($request->transaction_type)
            ->setMethod($request->method)
            ->setEmiMonth($request->emi_month)
            ->setInterest($request->interest)
            ->create();
    }
}
