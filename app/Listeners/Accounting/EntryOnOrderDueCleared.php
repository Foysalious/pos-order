<?php namespace App\Listeners\Accounting;

use App\Events\OrderDueCleared;
use App\Events\OrderUpdated;
use App\Services\EventNotification\Events;
use App\Services\Order\Constants\PaymentMethods;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Order\Accounting\EntryOnOrderDueCleared as OrderDueClearedJob;

class EntryOnOrderDueCleared
{
    use DispatchesJobs, SerializesModels, AccountingEventNotification;

    /**
     * Handle the event.
     *
     * @param OrderDueCleared|OrderUpdated $event
     * @return void
     */
    public function handle(OrderDueCleared|OrderUpdated $event)
    {
        if (get_class($event) == OrderUpdated::class) {
            if (!empty($event->getPaymentInfo())) {
                $paid_amount = $event->getPaymentInfo()['paid_amount'];
                $payment_method = $event->getPaymentInfo()['payment_method'];
                if ($paid_amount < 0 || $payment_method == PaymentMethods::PAYMENT_LINK) return;
                if (is_null($paid_amount) || $paid_amount == 0) return;
                if (!$event->getOrder()->customer_id) return;
                $event_notification = $this->createEventNotification($event->getOrder(), Events::ORDER_UPDATE);
                $this->dispatch(new OrderDueClearedJob($event->getOrder(), $paid_amount, $event_notification));
            }
        } elseif (get_class($event) == OrderDueCleared::class) {
            if (!$event->getPaidAmount() || $event->getPaidAmount() == 0) return;
            if (!$event->getOrder()->customer_id) return;
            $event_notification = $this->createEventNotification($event->getOrder(), Events::ORDER_UPDATE);
            $this->dispatch(new OrderDueClearedJob($event->getOrder(), $event->getPaidAmount(), $event_notification));
        }

    }
}
