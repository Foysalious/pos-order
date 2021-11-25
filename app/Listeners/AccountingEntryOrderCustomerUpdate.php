<?php namespace App\Listeners;

use App\Events\OrderCustomerUpdated;
use App\Services\Accounting\CustomerUpdateEntry;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;

class AccountingEntryOrderCustomerUpdate
{
    protected CustomerUpdateEntry $updateEntry;

    /**
     * AccountingEntryOnOrderUpdating constructor.
     * @param CustomerUpdateEntry $updateEntry
     */
    public function __construct(CustomerUpdateEntry $updateEntry)
    {
        $this->updateEntry = $updateEntry;
    }


    /**
     * Handle the event.
     *
     * @param OrderCustomerUpdated $event
     * @return void
     * @throws AccountingEntryServerError
     */
    public function handle(OrderCustomerUpdated $event)
    {
        $this->updateEntry
            ->setOrder($event->getOrder())
            ->customerUpdateEntry();
    }
}
