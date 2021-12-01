<?php namespace App\Listeners\Accounting;

use App\Events\OrderDeleted;
use App\Services\Accounting\DeleteEntry;

class EntryOnOrderDelete
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
