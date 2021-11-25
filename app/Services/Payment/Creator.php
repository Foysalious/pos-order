<?php namespace App\Services\Payment;


use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\PaymentRepositoryInterface;
use App\Services\Order\Constants\OrderLogTypes;
use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\OrderLogCreator;
use App\Services\Order\PriceCalculation;
use App\Services\OrderLog\Objects\OrderObject;
use App\Traits\ModificationFields;
use Carbon\Carbon;

class Creator
{
    use ModificationFields;
    private PaymentRepositoryInterface $paymentRepositoryInterface;
    private $orderId;
    private $amount;
    private $transactionType;
    private $method;
    private $method_details;
    private $emiMonth;
    private $interest;

    /**
     * Creator constructor.
     * @param PaymentRepositoryInterface $paymentRepositoryInterface
     */
    public function __construct(
        PaymentRepositoryInterface $paymentRepositoryInterface,
        protected OrderRepositoryInterface $orderRepository,
        private OrderLogCreator $orderLogCreator)
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
     * @param mixed $method_details
     * @return Creator
     */
    public function setMethodDetails($method_details)
    {
        $this->method_details = $method_details;
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
        $order = $this->orderRepository->find($this->orderId);
        $previous_order = $this->setExistingOrder($order);
        $payment = $this->paymentRepositoryInterface->create($this->makeCreateData());
        /** @var OrderObject $oldOrderObject */
        $oldOrderObject = app(OrderObject::class);
        $oldOrderObject->setOrder($previous_order);
        /** @var OrderObject $oldOrderObject */
        $newOrderObject = app(OrderObject::class);
        $newOrderObject->setOrder($order->refresh());
        $this->orderLogCreator->setOrderId($order->id)->setType(OrderLogTypes::PAYMENTS)->setExistingOrderData(json_encode($oldOrderObject))
            ->setChangedOrderData(json_encode($newOrderObject))->create();
        $order = $this->orderRepository->find($this->orderId);
        /** @var PriceCalculation $priceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        $paid_at = $payment->created_at ?: Carbon::now();
        if (! $priceCalculation->setOrder($order)->getDue() && !$order->paid_at)
            $this->orderRepository->update($order, ['paid_at' => $paid_at] + $this->modificationFields(false,true));
        return $payment;
    }

    private function makeCreateData()
    {
        $data = [];
        $data['order_id'] = $this->orderId;
        $data['amount'] = $this->amount;
        $data['transaction_type'] = $this->transactionType;
        $data['method'] = $this->method;
        $data['method_details'] = $this->method_details;
        $data['emi_month'] = $this->emiMonth;
        $data['interest'] = $this->interest;
        return $data + $this->modificationFields(true,false);
    }

    private function setExistingOrder($order)
    {
        $previous_order = clone $order;
        $order = $previous_order;
        $order->items = $previous_order->items;
        $order->customer = $previous_order->customer;
        $order->payments = $previous_order->payments;
        $order->discounts = $previous_order->discounts;
        return $previous_order;
    }

}
