<?php namespace App\Services\Payment;


use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\PaymentRepositoryInterface;
use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\PriceCalculation;
use Carbon\Carbon;

class Creator
{
    private PaymentRepositoryInterface $paymentRepositoryInterface;
    private $orderId;
    private $amount;
    private $transactionType;
    private $method;
    private $emiMonth;
    private $interest;

    /**
     * Creator constructor.
     * @param PaymentRepositoryInterface $paymentRepositoryInterface
     */
    public function __construct(PaymentRepositoryInterface $paymentRepositoryInterface, protected OrderRepositoryInterface $orderRepository)
    {
        $this->paymentRepositoryInterface = $paymentRepositoryInterface;
        $this->method = PaymentMethods::CASH_ON_DELIVERY;
    }

    /**
     * @param mixed $orderId
     * @return Creator
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return Creator
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $transactionType
     * @return Creator
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * @param mixed $method
     * @return Creator
     */
    public function setMethod($method)
    {
        $this->method = is_null($method) ? PaymentMethods::CASH_ON_DELIVERY : $method;
        return $this;
    }

    /**
     * @param mixed $emiMonth
     * @return Creator
     */
    public function setEmiMonth($emiMonth)
    {
        $this->emiMonth = $emiMonth;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return Creator
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    public function create()
    {
        $payment = $this->paymentRepositoryInterface->create($this->makeCreateData());
        $order = $this->orderRepository->find($this->orderId);
        /** @var PriceCalculation $priceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        $closed_and_paid_at = $payment->created_at ?: Carbon::now();
        if (! $priceCalculation->setOrder($order)->getDue() && !$order->closed_and_paid_at)
            $this->orderRepository->update($order, ['closed_and_paid_at' => $closed_and_paid_at]);
        return $payment;
    }

    private function makeCreateData()
    {
        $data = [];
        $data['order_id'] = $this->orderId;
        $data['amount'] = $this->amount;
        $data['transaction_type'] = $this->transactionType;
        $data['method'] = $this->method;
        $data['emi_month'] = $this->emiMonth;
        $data['interest'] = $this->interest;
        return $data;
    }

}
