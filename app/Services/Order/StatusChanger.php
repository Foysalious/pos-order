<?php namespace App\Services\Order;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Repositories\ExpenseTracker\AutomaticEntryRepository;
use App\Services\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\Payment\Creator as PaymentCreator;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\App;

class StatusChanger
{
    use ResponseAPI;
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
//        $this->orderRepositoryInterface->update($this->order, ['status' => $this->status]);
        /** @var PriceCalculation $order_calculator */
        $order_calculator = App::make(PriceCalculation::class);
        $order_calculator->setOrder($this->order);

        if ($this->order->sales_channel_id == SalesChannelIds::WEBSTORE) {
              if ($this->status == Statuses::DECLINED || $this->status == Statuses::CANCELLED) $this->refund();
              if ($this->status == Statuses::COMPLETED && $order_calculator->getDue() > 0) $this->collectPayment($this->order, $order_calculator );
          }
        return $this->success('Successful', ['order' => $this->order], 200);
    }


    private function refund()
    {

    }

    /**
     * @throws ExpenseTrackingServerError
     */
    private function collectPayment(Order $order, PriceCalculation $order_calculator)
    {
        $payment_data = [
            'order_id' => $order->id,
            'amount' => $order_calculator->getDue(),
            'method' => 'cod'
        ];
        if ($order->emi_month) $payment_data['emi_month'] = $order->emi_month;
//        $this->paymentCreator->credit($payment_data);
        $this->updateIncome($order, $order_calculator);
    }

    /**
     * @throws ExpenseTrackingServerError
     */
    private function updateIncome(Order $order, PriceCalculation $order_calculator)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry  = app(AutomaticEntryRepository::class);
        $amount = $order_calculator->getDiscountedPrice();
        $entry->setPartner($order->partner)
            ->setAmount($amount)
            ->setAmountCleared($order_calculator->getDue())
            ->setFor(EntryTypes::INCOME)
            ->setSourceType(class_basename($order))
            ->setSourceId($order->id)
            ->setCreatedAt($order->created_at)
            ->setEmiMonth($order->emi_month)
            ->setIsWebstoreOrder($order->sales_channel_id == SalesChannelIds::WEBSTORE ? 1 : 0)
            ->updateFromSrc();
        dd('entry inserted');
    }
}
