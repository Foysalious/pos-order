<?php namespace App\Listeners;


use App\Events\OrderDeleted;
use App\Jobs\Usage\UsageJob;
use App\Services\Usage\Types;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class UsageOnOrderDelete
{
    use DispatchesJobs,SerializesModels;

    /**
     * Handle the event.
     *
     * @param OrderDeleted $event
     * @return void
     */
    public function handle(OrderDeleted $event)
    {
        $this->dispatch((new UsageJob((int) $event->getOrder()->partner_id, Types::POS_ORDER_DELETE)));
    }
}
