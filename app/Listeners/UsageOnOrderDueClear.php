<?php namespace App\Listeners;

use App\Events\OrderTransactionCompleted;
use App\Jobs\Usage\UsageJob;
use App\Services\Usage\Types;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;


class UsageOnOrderDueClear
{
    use DispatchesJobs,SerializesModels;

    /**
     * Handle the event.
     *
     * @param OrderTransactionCompleted $event
     * @return void
     */
    public function handle(OrderTransactionCompleted $event)
    {
        $this->dispatch((new UsageJob((int) $event->getOrder()->partner->id, Types::POS_DUE_COLLECTION)));
    }
}
