<?php namespace App\Services\Order;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\Order\Constants\SalesChannels;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\Payment\Creator as PaymentCreator;

class StatusChanger
{
    protected $status;
    /** @var Order */
    protected $order;
    /** @var OrderRepositoryInterface */
    protected $orderRepositoryInterface;
    /** @var PaymentCreator */
    protected $paymentCreator;
    protected $modifier;


    public function __construct(OrderRepositoryInterface $orderRepositoryInterface, PaymentCreator $paymentCreator)
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->paymentCreator = $paymentCreator;
    }

    public function setOrder(Order $order)
    {
        $this->order = $order->calculate();
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    public function changeStatus()
    {
        $this->orderRepositoryInterface->update($this->order, ['status' => $this->status]);
        if ($this->order->sales_channel == SalesChannels::WEBSTORE) {
            if ($this->status == Statuses::DECLINED || $this->status == Statuses::CANCELLED) $this->refund();
            if ($this->status == Statuses::COMPLETED && $this->order->getDue()) $this->collectPayment($this->order);
        }
    }


    private function refund()
    {

    }

    private function collectPayment($order)
    {
        $payment_data = [
            'pos_order_id' => $order->id,
            'amount' => $order->getDue(),
            'method' => 'cod'
        ];
        if ($order->emi_month) $payment_data['emi_month'] = $order->emi_month;
        $this->paymentCreator->credit($payment_data);
        $order = $order->calculate();
        $order->payment_status = $order->getPaymentStatus();
        $this->updateIncome($order, $order->getDue(), $order->emi_month);
    }

    private function updateIncome(Order $order, $paid_amount, $emi_month)
    {

    }
}
