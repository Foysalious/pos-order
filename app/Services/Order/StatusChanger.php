<?php namespace App\Services\Order;

use App\Events\OrderDeleted;
use App\Events\OrderDueCleared;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\Statuses;
use App\Services\Payment\Creator as PaymentCreator;
use App\Services\Transaction\Constants\TransactionTypes;
use App\Traits\ModificationFields;
use Illuminate\Support\Facades\App;

class StatusChanger
{
    use ModificationFields;
    protected string $status;
    /** @var Order */
    protected Order $order;
    protected string $delivery_request_id;


    public function __construct(
        protected OrderRepositoryInterface      $orderRepository,
        protected PaymentCreator                $paymentCreator,
        protected StockRefillerForCanceledOrder $stockRefillForCanceledOrder,
        protected PriceCalculation              $orderCalculator
    )
    {}

    public function setOrder(Order $order) : StatusChanger
    {
        $this->order = $order;
        return $this;
    }

    public function setStatus($status) : StatusChanger
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string $delivery_request_id
     * @return StatusChanger
     */
    public function setDeliveryRequestId(string $delivery_request_id) : StatusChanger
    {
        $this->delivery_request_id = $delivery_request_id;
        return $this;
    }

    public function changeStatus()
    {
        $this->orderRepository->update($this->order, ['status' => $this->status]);
        /** @var PriceCalculation $order_calculator */
        $order_calculator = App::make(PriceCalculation::class);
        $order_calculator->setOrder($this->order);

        if ($this->order->sales_channel_id == SalesChannelIds::WEBSTORE) {
              if ($this->status == Statuses::DECLINED || $this->status == Statuses::CANCELLED) {
                  $this->cancelOrder();
              }
              else if ($this->status == Statuses::COMPLETED && $order_calculator->getDue() > 0) {
                  $this->collectPayment($this->order, $order_calculator );
              }
          }
    }


    private function cancelOrder()
    {
        $this->stockRefillForCanceledOrder->setOrder($this->order)->refillStock();
        $this->refundIfEligible();
        event(new OrderDeleted($this->order));
    }

    private function collectPayment(Order $order, PriceCalculation $order_calculator)
    {
        $this->paymentCreator->setOrderId($order->id)->setAmount($order_calculator->getDue())->setMethod(PaymentMethods::CASH_ON_DELIVERY)
            ->setTransactionType(TransactionTypes::CREDIT)->setEmiMonth($order->emi_month)
            ->setInterest($order->interest)->create();
        event(new OrderDueCleared($order));
    }

    public function updateStatusForIpn()
    {
        $this->order->update($this->withUpdateModificationField(['status' => Statuses::COMPLETED]));
    }

    private function refundIfEligible()
    {
        $this->orderCalculator->setOrder($this->order);
        $paid_amount = $this->orderCalculator->getPaid();
        if( $paid_amount > 0) {
            $this->paymentCreator->setOrderId($this->order->id);
            $this->paymentCreator->setAmount($paid_amount);
            $this->paymentCreator->setMethod(PaymentMethods::CASH_ON_DELIVERY);
            $this->paymentCreator->setTransactionType(TransactionTypes::DEBIT);
            $this->paymentCreator->create();
            $this->order->paid_at = null;
            $this->order->save();
        }
    }

}
