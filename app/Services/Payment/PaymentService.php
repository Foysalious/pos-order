<?php namespace App\Services\Payment;

use App\Http\Requests\PaymentRequest;
use App\Interfaces\OrderRepositoryInterface;
use App\Services\BaseService;
use App\Services\Order\PriceCalculation;
use Carbon\Carbon;

class PaymentService extends BaseService
{
    private Creator $creator;

    /**
     * PaymentService constructor.
     * @param Creator $creator
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(Creator $creator, private OrderRepositoryInterface $orderRepository)
    {
        $this->creator = $creator;
    }

    public function store(PaymentRequest $request)
    {
        $payment = $this->creator->setOrderId($request->pos_order_id)
            ->setAmount($request->amount)
            ->setTransactionType($request->transaction_type)
            ->setMethod($request->payment_method)
            ->setEmiMonth($request->emi_month)
            ->setInterest($request->interest)
            ->create();
        $order = $this->orderRepository->find($request->pos_order_id);
        /** @var PriceCalculation $priceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        $closed_and_paid_at = $payment->created_at ?: Carbon::now();
        if (!$priceCalculation->getDue() && !$order->closed_and_paid_at) $this->orderRepository->update($order, ['closed_and_paid_at' => $closed_and_paid_at]);
    }
}
