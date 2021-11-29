<?php namespace App\Listeners;

use App\Events\OrderDueCleared;
use App\Services\Accounting\OrderDueEntry;

class AccountingEntryOnOrderDueCleared
{
    protected OrderDueEntry $dueEntry;

    public function __construct(OrderDueEntry $createEntry)
    {
        $this->dueEntry = $createEntry;
    }

    /**
     * Handle the event.
     *
     * @param  OrderDueCleared  $event
     * @return void
     */
    public function handle(OrderDueCleared $event)
    {
        dd($event, 'here');
        $this->dueEntry->setOrder($event->getOrder())->create();
    }
}
