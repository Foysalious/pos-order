<?php namespace App\Listeners;

use App\Events\OrderTransactionCompleted;
use App\Events\OrderUpdated;
use App\Services\Accounting\UpdateEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AccountingEntryOnOrderUpdating
{
    protected UpdateEntry $updateEntry;

    /**
     * AccountingEntryOnOrderUpdating constructor.
     * @param UpdateEntry $updateEntry
     */
    public function __construct(UpdateEntry $updateEntry)
    {
        $this->updateEntry = $updateEntry;
    }


    /**
     * Handle the event.
     *
     * @param  OrderUpdated  $event
     * @return void
     */
    public function handle(OrderUpdated $event)
    {
        $this->updateEntry
            ->setOrder($event->getOrder())
            ->setOrderProductChangeData($event->getOrderProductChangedData())
            ->update();
    }
}
