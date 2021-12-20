<?php namespace App\Listeners\Accounting;

use App\Events\OrderDueCleared;
use App\Events\OrderUpdated;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\Accounting\OrderDueEntry;

class EntryOnOrderDueCleared
{
    protected OrderDueEntry $dueEntry;

    public function __construct(OrderDueEntry $dueEntry)
    {
        $this->dueEntry = $dueEntry;
    }

    /**
     * Handle the event.
     *
     * @param OrderDueCleared|OrderUpdated $event
     * @return void
     * @throws AccountingEntryServerError
     */
    public function handle(OrderDueCleared|OrderUpdated $event)
    {
        if (get_class($event) == OrderUpdated::class) {
            if(empty($event->getOrderProductChangedData()) && !empty($event->getPaymentInfo())) {
                $paid_amount = $event->getPaymentInfo()['paid_amount'];
                if (is_null($paid_amount) || $paid_amount == 0) return;
                $this->dueEntry->setOrder($event->getOrder())->setPaidAmount($paid_amount)->create();
            }
        } elseif (get_class($event) == OrderDueCleared::class){
            if (!$event->getPaidAmount() || $event->getPaidAmount() == 0) return;
            $this->dueEntry->setOrder($event->getOrder())->setPaidAmount($event->getPaidAmount())->create();
        }

    }
}
