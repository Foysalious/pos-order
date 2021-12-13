<?php namespace App\Listeners\Accounting;

use App\Events\OrderCustomerUpdated;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Jobs\Order\Accounting\EntryOnOrderCustomerUpdate as CustomerUpdateEntryJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class EntryOnOrderCustomerUpdate
{
    use DispatchesJobs,SerializesModels;
    /**
     * Handle the event.
     *
     * @param OrderCustomerUpdated $event
     * @return void
     * @throws AccountingEntryServerError
     */
    public function handle(OrderCustomerUpdated $event)
    {
        $this->dispatch(new CustomerUpdateEntryJob($event->getOrder()));
    }
}
