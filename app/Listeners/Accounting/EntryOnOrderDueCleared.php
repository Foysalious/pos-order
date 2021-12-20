<?php namespace App\Listeners\Accounting;

use App\Events\OrderDueCleared;
use App\Events\OrderUpdated;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Order\Accounting\EntryOnOrderDueCleared as OrderDueClearedJob;

class EntryOnOrderDueCleared
{
    use DispatchesJobs,SerializesModels;

    /**
     * Handle the event.
     *
     * @param OrderDueCleared|OrderUpdated $event
     * @return void
     */
    public function handle(OrderDueCleared|OrderUpdated $event)
    {
        if (get_class($event) == OrderUpdated::class) {
            if(empty($event->getOrderProductChangedData()) && !empty($event->getPaymentInfo())) {
                $paid_amount = $event->getPaymentInfo()['paid_amount'];
                if (is_null($paid_amount) || $paid_amount == 0) return;
                $this->dispatch(new OrderDueClearedJob($event->getOrder(),$paid_amount));
            }
        } elseif (get_class($event) == OrderDueCleared::class){
            if (!$event->getPaidAmount() || $event->getPaidAmount() == 0) return;
            $this->dispatch(new OrderDueClearedJob($event->getOrder(),$event->getPaidAmount()));
        }

    }
}
