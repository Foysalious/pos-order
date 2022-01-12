<?php namespace App\Listeners\Accounting;

use App\Events\OrderDueCleared;
use App\Events\OrderUpdated;
use App\Services\EventNotification\Events;
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
        $event_notification = $this->createEventNotification($event->getOrder(), Events::ORDER_UPDATE);
        if (get_class($event) == OrderUpdated::class) {
            if (empty($event->getOrderProductChangedData()) && !empty($event->getPaymentInfo())) {
                $paid_amount = $event->getPaymentInfo()['paid_amount'];
                if (is_null($paid_amount) || $paid_amount == 0) return;
                $this->dispatch(new OrderDueClearedJob($event->getOrder(), $paid_amount, $event_notification));
            }
        } elseif (get_class($event) == OrderDueCleared::class) {
            if (!$event->getPaidAmount() || $event->getPaidAmount() == 0) return;
            $this->dispatch(new OrderDueClearedJob($event->getOrder(), $event->getPaidAmount(), $event_notification));
        }

    }
}
