<?php namespace App\Listeners;

use App\Events\OrderDueCleared;
use App\Events\OrderUpdated;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\Accounting\OrderDueEntry;

class AccountingEntryOnOrderDueCleared
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
                $this->dueEntry->setOrder($event->getOrder())->setPaidAmount($paid_amount)->create();
            }
        } elseif (get_class($event) == OrderDueCleared::class){
            $this->dueEntry->setOrder($event->getOrder())->setPaidAmount($event->getPaidAmount())->create();
        }

    }
}
