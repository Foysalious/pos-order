<?php namespace App\Listeners;

use App\Events\OrderPlaceTransactionCompleted;
use App\Services\Accounting\CreateEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AccountingEntryOnOrderCreation
{
    protected CreateEntry $createEntry;

    public function __construct(CreateEntry $createEntry)
    {
        $this->createEntry = $createEntry;
    }

    /**
     * Handle the event.
     *
     * @param  OrderPlaceTransactionCompleted  $event
     * @return void
     */
    public function handle(OrderPlaceTransactionCompleted $event)
    {
        $this->createEntry->setOrder($event->getOrder())->create();
    }
}
