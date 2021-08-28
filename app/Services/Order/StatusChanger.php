<?php namespace App\Services\Order;

use App\Events\OrderDueCleared;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\Statuses;
use App\Services\Payment\Creator as PaymentCreator;
use App\Services\Transaction\Constants\TransactionTypes;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\App;

class StatusChanger
{
    use ResponseAPI;
    protected $status;
    /** @var Order */
    protected $order;
    protected $modifier;


    public function __construct(
        protected OrderRepositoryInterface $orderRepositoryInterface,
        protected PaymentCreator $paymentCreator){}

    public function setOrder(Order $order)
    {
        $this->order = $order;
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
        /** @var PriceCalculation $order_calculator */
        $order_calculator = App::make(PriceCalculation::class);
        $order_calculator->setOrder($this->order);

        if ($this->order->sales_channel_id == SalesChannelIds::WEBSTORE) {
              if ($this->status == Statuses::DECLINED || $this->status == Statuses::CANCELLED) {
                  $this->refund();
              }
              if ($this->status == Statuses::COMPLETED && $order_calculator->getDue() > 0) {
                  $this->collectPayment($this->order, $order_calculator );
              }
          }
        return $this->success('Successful', ['order' => $this->order], 200);
    }


    private function refund()
    {

    }

    private function collectPayment(Order $order, PriceCalculation $order_calculator)
    {
        $this->paymentCreator->setOrderId($order->id)->setAmount($order_calculator->getDue())->setMethod(PaymentMethods::CASH_ON_DELIVERY)
            ->setTransactionType(TransactionTypes::CREDIT)->setEmiMonth($order->emi_month)
            ->setInterest($order->interest)->create();
        event(new OrderDueCleared($order));
    }
}
