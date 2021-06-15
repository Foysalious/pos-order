<?php namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Accounting\Creator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AccountingEntryOnOrderCreation
{
    protected Creator $creator;

    public function __construct(Creator $creator)
    {
        $this->creator = $creator;
    }

    /**
     * Handle the event.
     *
     * @param  OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        $this->creator->setOrder($event->getOrder())->create();
    }
}
