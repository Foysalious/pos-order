<?php namespace App\Listeners\Accounting;

use App\Events\OrderUpdated;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\Accounting\UpdateEntry;

class EntryOnOrderUpdating
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
     * @param OrderUpdated $event
     * @return void
     * @throws AccountingEntryServerError
     */
    public function handle(OrderUpdated $event)
    {
        if (!empty($event->getOrderProductChangedData())) {
            $this->updateEntry
                ->setOrder($event->getOrder())
                ->setOrderProductChangeData($event->getOrderProductChangedData())
                ->update();
        }
    }
}
