<?php namespace App\Listeners;


use App\Events\OrderDeleted;
use App\Services\Accounting\DeleteEntry;

class AccountingEntryOnOrderDelete
{
    protected DeleteEntry $deleteEntry;

    public function __construct(DeleteEntry $deleteEntry)
    {
        $this->deleteEntry = $deleteEntry;
    }

    /**
     * Handle the event.
     *
     * @param OrderDeleted $event
     * @return void
     */
    public function handle(OrderDeleted $event)
    {
        $this->deleteEntry->setOrder($event->getOrder())->delete();
    }
}
