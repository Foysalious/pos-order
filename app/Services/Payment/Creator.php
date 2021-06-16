<?php namespace App\Services\Payment;


use App\Interfaces\PaymentRepositoryInterface;

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
    public function __construct(PaymentRepositoryInterface $paymentRepositoryInterface)
    {
        $this->paymentRepositoryInterface = $paymentRepositoryInterface;
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
        $this->method = $method;
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
        return $this->paymentRepositoryInterface->create($this->makeCreateData());
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
